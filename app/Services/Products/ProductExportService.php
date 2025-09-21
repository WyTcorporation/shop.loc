<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductExport;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;
use Throwable;

class ProductExportService
{
    public const DEFAULT_CHUNK_SIZE = 200;

    public function __construct(
        protected int $chunkSize = self::DEFAULT_CHUNK_SIZE,
    ) {
    }

    public function start(ProductExport $export): void
    {
        try {
            $export->forceFill([
                'status' => 'processing',
                'processed_rows' => 0,
                'total_rows' => 0,
                'message' => null,
                'completed_at' => null,
            ])->save();

            $query = $this->buildQuery($export);
            $total = (clone $query)->count();

            $export->forceFill(['total_rows' => $total])->save();

            if ($total === 0) {
                $export->forceFill([
                    'status' => 'completed',
                    'file_path' => null,
                    'completed_at' => now(),
                ])->save();

                Notification::make()
                    ->title(__('shop.admin.resources.products.exports.messages.completed_title'))
                    ->body(__('shop.admin.resources.products.exports.messages.completed_empty'))
                    ->success()
                    ->sendToDatabase($export->user);

                return;
            }

            if ($export->format === 'xlsx') {
                $path = $this->exportToXlsx($export, $query);
            } else {
                $path = $this->exportToCsv($export, $query);
            }

            $export->forceFill([
                'status' => 'completed',
                'file_path' => $path,
                'completed_at' => now(),
            ])->save();

            Notification::make()
                ->title(__('shop.admin.resources.products.exports.messages.completed_title'))
                ->body(__('shop.admin.resources.products.exports.messages.completed_ready'))
                ->success()
                ->sendToDatabase($export->user);
        } catch (Throwable $exception) {
            Log::error('Product export failed', [
                'export_id' => $export->id,
                'exception' => $exception,
            ]);

            $export->forceFill([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'completed_at' => now(),
            ])->save();

            Notification::make()
                ->title(__('shop.admin.resources.products.exports.messages.failed_title'))
                ->body($exception->getMessage())
                ->danger()
                ->sendToDatabase($export->user);
        }
    }

    protected function buildQuery(ProductExport $export)
    {
        $query = Product::query()->orderBy('id');
        $filters = $export->filters ?? [];

        if (($filters['only_active'] ?? false) === true) {
            $query->where('is_active', true);
        }

        if (isset($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query;
    }

    protected function exportToCsv(ProductExport $export, $query): string
    {
        $fileName = $this->resolveFileName($export, 'csv');
        $path = 'exports/products/' . $fileName;
        $disk = Storage::disk($export->disk);
        $disk->makeDirectory('exports/products');
        $disk->put($path, '');
        $fullPath = $disk->path($path);

        $handle = fopen($fullPath, 'w');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open export file for writing.');
        }

        $headers = $this->columns();
        fputcsv($handle, $headers);

        $processed = 0;

        $query->chunkById($this->chunkSize, function ($products) use ($handle, &$processed) {
            foreach ($products as $product) {
                $row = $this->mapProductToRow($product);
                fputcsv($handle, $row);
                $processed++;
            }
        });

        fclose($handle);

        $export->forceFill(['processed_rows' => $processed])->save();

        return $path;
    }

    protected function exportToXlsx(ProductExport $export, $query): string
    {
        $fileName = $this->resolveFileName($export, 'xlsx');
        $path = 'exports/products/' . $fileName;
        $disk = Storage::disk($export->disk);
        $disk->makeDirectory('exports/products');

        $rowsXml = '';
        $rowNumber = 1;

        $headers = $this->columns();
        $rowsXml .= $this->buildRowXml($rowNumber++, $headers, true);

        $processed = 0;

        $query->chunkById($this->chunkSize, function ($products) use (&$rowsXml, &$rowNumber, &$processed) {
            foreach ($products as $product) {
                $row = $this->mapProductToRow($product);
                $rowsXml .= $this->buildRowXml($rowNumber++, $row, false);
                $processed++;
            }
        });

        $export->forceFill(['processed_rows' => $processed])->save();

        $fullPath = $disk->path($path);

        $zip = new ZipArchive();

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to initialise XLSX archive.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->relsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rowsXml));
        $zip->close();

        return $path;
    }

    protected function resolveFileName(ProductExport $export, string $extension): string
    {
        $base = $export->file_name ?: 'products_export_' . now()->format('Y_m_d_His');
        $base = Str::slug($base, '_');

        return $base . '.' . $extension;
    }

    protected function columns(): array
    {
        return [
            'sku',
            'name',
            'description',
            'price',
            'price_old',
            'stock',
            'category_id',
            'vendor_id',
            'is_active',
        ];
    }

    protected function mapProductToRow(Product $product): array
    {
        return [
            $product->sku,
            $product->name,
            $product->description,
            $product->price,
            $product->price_old,
            $product->stock,
            $product->category_id,
            $product->vendor_id,
            $product->is_active ? 1 : 0,
        ];
    }

    protected function buildRowXml(int $rowNumber, array $values, bool $isHeader): string
    {
        $cells = [];
        $columnIndex = 0;

        foreach ($values as $value) {
            $columnIndex++;
            $columnLetter = $this->columnLetter($columnIndex);
            $cellReference = $columnLetter . $rowNumber;
            $escaped = htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $cellValue = $isHeader
                ? "<is><t>{$escaped}</t></is>"
                : '<v>' . $escaped . '</v>';

            $cells[] = $isHeader
                ? "<c r=\"{$cellReference}\" t=\"inlineStr\">{$cellValue}</c>"
                : "<c r=\"{$cellReference}\" t=\"str\">{$cellValue}</c>";
        }

        return '<row r="' . $rowNumber . '">' . implode('', $cells) . '</row>';
    }

    protected function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(ord('A') + ($index % 26)) . $letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }

    protected function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    protected function relsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    protected function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Products" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    protected function workbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    protected function worksheetXml(string $rowsXml): string
    {
        $template = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>
        {{rows}}
    </sheetData>
</worksheet>
XML;

        return str_replace('{{rows}}', $rowsXml, $template);
    }
}
