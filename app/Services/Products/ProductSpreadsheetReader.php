<?php

namespace App\Services\Products;

use RuntimeException;
use SplFileObject;
use ZipArchive;

class ProductSpreadsheetReader
{
    protected function __construct(
        protected string $path,
        protected string $extension,
    ) {
    }

    public static function make(string $path): self
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            throw new RuntimeException('Unsupported file extension: ' . $extension);
        }

        return new self($path, $extension);
    }

    public function getHeaders(): array
    {
        return $this->readHeaders();
    }

    public function getTotalRows(): int
    {
        if ($this->extension === 'csv') {
            return max(0, $this->countCsvRows() - 1);
        }

        return max(0, $this->countXlsxRows() - 1);
    }

    public function getRows(int $offset, int $limit): array
    {
        $rows = [];
        $count = 0;
        $skipped = 0;

        foreach ($this->iterateRows() as $row) {
            if ($skipped++ < $offset) {
                continue;
            }

            $rows[] = $row;
            $count++;

            if ($count >= $limit) {
                break;
            }
        }

        return $rows;
    }

    protected function readHeaders(): array
    {
        foreach ($this->iterateRawRows() as $row) {
            $headers = array_map(fn ($value) => $this->normalizeHeader($value), $row);

            return array_values(array_filter($headers));
        }

        return [];
    }

    protected function iterateRows(): \Generator
    {
        $headers = null;

        foreach ($this->iterateRawRows() as $row) {
            if ($headers === null) {
                $headers = array_map(fn ($value) => $this->normalizeHeader($value), $row);
                continue;
            }

            $assoc = [];

            foreach ($headers as $index => $header) {
                if ($header === null || $header === '') {
                    continue;
                }

                $assoc[$header] = isset($row[$index]) ? $this->normalizeValue($row[$index]) : null;
            }

            if ($assoc !== [] && array_filter($assoc, fn ($value) => $value !== null && $value !== '') !== []) {
                yield $assoc;
            }
        }
    }

    protected function iterateRawRows(): \Generator
    {
        if ($this->extension === 'csv') {
            yield from $this->iterateCsv();
        } else {
            yield from $this->iterateXlsx();
        }
    }

    protected function iterateCsv(): \Generator
    {
        $file = new SplFileObject($this->path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);

        foreach ($file as $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $normalized = array_map(fn ($value) => $this->normalizeValue($value), $row);

            if ($normalized === [] || $this->isRowEmpty($normalized)) {
                continue;
            }

            yield array_values($normalized);
        }
    }

    protected function iterateXlsx(): \Generator
    {
        $archive = new ZipArchive();

        if ($archive->open($this->path) !== true) {
            throw new RuntimeException('Unable to open spreadsheet archive.');
        }

        $sheetPath = $this->resolveWorksheetPath($archive);
        $sheetXml = $archive->getFromName($sheetPath);

        if ($sheetXml === false) {
            $archive->close();
            throw new RuntimeException('Worksheet not found in spreadsheet.');
        }

        $sharedStrings = $this->readSharedStrings($archive);
        $archive->close();

        $document = simplexml_load_string($sheetXml);

        if (! $document) {
            throw new RuntimeException('Unable to parse worksheet.');
        }

        if (! isset($document->sheetData->row)) {
            return;
        }

        foreach ($document->sheetData->row as $row) {
            $cells = [];

            if (! isset($row->c)) {
                continue;
            }

            foreach ($row->c as $cell) {
                $reference = (string) ($cell['r'] ?? '');
                $index = $this->columnIndexFromReference($reference);
                $value = $this->readCellValue($cell, $sharedStrings);

                $cells[$index] = $value;
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);

            $normalized = [];
            $maxIndex = array_key_last($cells);

            for ($i = 0; $i <= $maxIndex; $i++) {
                $normalized[] = $cells[$i] ?? null;
            }

            if ($this->isRowEmpty($normalized)) {
                continue;
            }

            yield $normalized;
        }
    }

    protected function countCsvRows(): int
    {
        $file = new SplFileObject($this->path, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key() + 1;
    }

    protected function countXlsxRows(): int
    {
        $count = 0;

        foreach ($this->iterateXlsx() as $_row) {
            $count++;
        }

        return $count;
    }

    protected function resolveWorksheetPath(ZipArchive $archive): string
    {
        $workbookXml = $archive->getFromName('xl/workbook.xml');

        if ($workbookXml === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = simplexml_load_string($workbookXml);

        if (! $workbook || ! isset($workbook->sheets->sheet[0])) {
            return 'xl/worksheets/sheet1.xml';
        }

        $sheet = $workbook->sheets->sheet[0];
        $relationshipId = (string) ($sheet['r:id'] ?? '');

        if ($relationshipId === '') {
            return 'xl/worksheets/sheet1.xml';
        }

        $relsXml = $archive->getFromName('xl/_rels/workbook.xml.rels');

        if ($relsXml === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $relationships = simplexml_load_string($relsXml);

        if (! $relationships) {
            return 'xl/worksheets/sheet1.xml';
        }

        foreach ($relationships->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationshipId) {
                $target = (string) $relationship['Target'];

                return 'xl/' . ltrim($target, '/');
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    protected function readSharedStrings(ZipArchive $archive): array
    {
        $sharedXml = $archive->getFromName('xl/sharedStrings.xml');

        if ($sharedXml === false) {
            return [];
        }

        $document = simplexml_load_string($sharedXml);

        if (! $document) {
            return [];
        }

        $strings = [];

        foreach ($document->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            if (isset($item->r)) {
                $buffer = '';

                foreach ($item->r as $run) {
                    $buffer .= (string) ($run->t ?? '');
                }

                $strings[] = $buffer;
            }
        }

        return $strings;
    }

    protected function readCellValue(\SimpleXMLElement $cell, array $sharedStrings): ?string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 's') {
            $index = (int) ($cell->v ?? 0);

            return $sharedStrings[$index] ?? null;
        }

        if ($type === 'inlineStr') {
            return isset($cell->is->t) ? (string) $cell->is->t : null;
        }

        return isset($cell->v) ? (string) $cell->v : null;
    }

    protected function normalizeHeader(?string $value): ?string
    {
        $value = $this->normalizeValue($value);

        return $value !== '' ? $value : null;
    }

    protected function normalizeValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = is_scalar($value) ? (string) $value : null;

        if ($string === null) {
            return null;
        }

        $string = trim($string);

        if ($string === '') {
            return null;
        }

        if (str_starts_with($string, '\ufeff')) {
            $string = ltrim($string, '\ufeff');
        }

        return $string;
    }

    protected function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    protected function columnIndexFromReference(string $reference): int
    {
        $letters = strtoupper(preg_replace('/[^A-Z]/', '', $reference));

        if ($letters === '') {
            return 0;
        }

        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - ord('A') + 1);
        }

        return $index - 1;
    }
}
