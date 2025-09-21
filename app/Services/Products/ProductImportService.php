<?php

namespace App\Services\Products;

use App\Jobs\FinalizeProductImport;
use App\Jobs\ProcessProductImportChunk;
use App\Models\Product;
use App\Models\ProductImport;
use App\Models\ProductImportLog;
use Filament\Notifications\Notification;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ProductImportService
{
    public const DEFAULT_CHUNK_SIZE = 200;

    public function __construct(
        protected int $chunkSize = self::DEFAULT_CHUNK_SIZE,
    ) {
    }

    public function start(ProductImport $import): void
    {
        $path = Storage::disk($import->disk)->path($import->file_path);

        $reader = ProductSpreadsheetReader::make($path);

        $headers = $reader->getHeaders();

        if ($headers === []) {
            $this->markAsFailed($import->id, __('shop.admin.resources.products.imports.messages.no_rows'));

            return;
        }

        $totalRows = $reader->getTotalRows();

        $import->forceFill([
            'status' => 'processing',
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'created_rows' => 0,
            'updated_rows' => 0,
            'failed_rows' => 0,
            'meta' => array_merge($import->meta ?? [], [
                'headers' => $headers,
            ]),
            'message' => null,
            'completed_at' => null,
        ])->save();

        if ($totalRows === 0) {
            $this->finalize($import->fresh());

            return;
        }

        $jobs = [];

        for ($offset = 0; $offset < $totalRows; $offset += $this->chunkSize) {
            $jobs[] = new ProcessProductImportChunk($import->id, $offset, $this->chunkSize);
        }

        $batch = Bus::batch($jobs)
            ->name('Product import #' . $import->id)
            ->then(function (Batch $batch) use ($import) {
                FinalizeProductImport::dispatch($import->id);
            })
            ->catch(function (Batch $batch, Throwable $exception) use ($import) {
                $this->markAsFailed($import->id, $exception->getMessage());
            })
            ->dispatch();

        $import->forceFill(['batch_id' => $batch->id])->save();
    }

    public function processChunk(ProductImport $import, int $offset, int $limit): void
    {
        $path = Storage::disk($import->disk)->path($import->file_path);
        $reader = ProductSpreadsheetReader::make($path);

        $rows = $reader->getRows($offset, $limit);

        $created = 0;
        $updated = 0;
        $failed = 0;

        $logs = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $offset + $index + 2; // account for header row

            try {
                $result = $this->processRow($row);
                $status = $result['status'];
                $message = $result['message'] ?? null;

                if ($status === 'created') {
                    $created++;
                } elseif ($status === 'updated') {
                    $updated++;
                } else {
                    $failed++;
                }
            } catch (Throwable $exception) {
                $status = 'failed';
                $message = $exception->getMessage();
                $failed++;
                Log::error('Product import row failed.', [
                    'import_id' => $import->id,
                    'row_number' => $rowNumber,
                    'exception' => $exception,
                ]);
            }

            $logs[] = [
                'product_import_id' => $import->id,
                'row_number' => $rowNumber,
                'status' => $status,
                'message' => $message,
                'payload' => $row,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($logs !== []) {
            ProductImportLog::query()->insert($logs);
        }

        $processed = count($rows);

        ProductImport::query()
            ->whereKey($import->id)
            ->update([
                'processed_rows' => DB::raw('processed_rows + ' . $processed),
                'created_rows' => DB::raw('created_rows + ' . $created),
                'updated_rows' => DB::raw('updated_rows + ' . $updated),
                'failed_rows' => DB::raw('failed_rows + ' . $failed),
                'updated_at' => now(),
            ]);
    }

    public function finalize(ProductImport $import): void
    {
        $import->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();

        Notification::make()
            ->title(__('shop.admin.resources.products.imports.messages.completed_title'))
            ->body(__('shop.admin.resources.products.imports.messages.completed_body', [
                'processed' => $import->processed_rows,
                'total' => $import->total_rows,
            ]))
            ->success()
            ->sendToDatabase($import->user);
    }

    public function markAsFailed(int $importId, string $message): void
    {
        $import = ProductImport::query()->find($importId);

        if (! $import) {
            return;
        }

        $import->forceFill([
            'status' => 'failed',
            'message' => $message,
            'completed_at' => now(),
        ])->save();

        Notification::make()
            ->title(__('shop.admin.resources.products.imports.messages.failed_title'))
            ->body($message)
            ->danger()
            ->sendToDatabase($import->user);
    }

    protected function processRow(array $row): array
    {
        $validator = Validator::make($row, [
            'sku' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_old' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['nullable', 'integer'],
            'vendor_id' => ['nullable', 'integer'],
            'is_active' => ['nullable'],
            'attributes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'failed',
                'message' => implode(' ', $validator->errors()->all()),
            ];
        }

        $data = $validator->validated();
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => array_key_exists('price', $data) && $data['price'] !== null ? (float) $data['price'] : null,
            'price_old' => array_key_exists('price_old', $data) && $data['price_old'] !== null ? (float) $data['price_old'] : null,
            'stock' => array_key_exists('stock', $data) && $data['stock'] !== null ? (int) $data['stock'] : null,
            'category_id' => $data['category_id'] ?? null,
            'vendor_id' => $data['vendor_id'] ?? null,
            'is_active' => array_key_exists('is_active', $data)
                ? (bool) (filter_var($data['is_active'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false)
                : true,
        ];

        if (! empty($data['attributes'])) {
            $decoded = json_decode($data['attributes'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $payload['attributes'] = $decoded;
            }
        }

        $product = Product::query()->where('sku', $data['sku'])->first();

        if (! $product) {
            $payload['slug'] = Str::slug($payload['name']);
            $payload['sku'] = $data['sku'];
            $product = Product::query()->create($payload);

            return [
                'status' => 'created',
                'message' => __('shop.admin.resources.products.imports.messages.row_created', ['sku' => $data['sku']]),
            ];
        }

        $product->fill(array_filter($payload, fn ($value) => $value !== null));
        $product->save();

        return [
            'status' => 'updated',
            'message' => __('shop.admin.resources.products.imports.messages.row_updated', ['sku' => $data['sku']]),
        ];
    }
}
