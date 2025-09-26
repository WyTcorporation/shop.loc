<?php

namespace App\Services\Documents;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentExporter
{
    private const SUPPORTED_FORMATS = ['pdf', 'csv', 'xml'];

    public static function download(Model $document, string $format, ?string $prefix = null): StreamedResponse
    {
        $payload = method_exists($document, 'toExportArray')
            ? $document->toExportArray()
            : $document->toArray();

        $nameSource = Arr::get($payload, 'number', (string) $document->getKey());
        $fileName = ($prefix ? Str::slug($prefix) . '-' : '') . Str::slug((string) $nameSource);
        $fileName = trim($fileName, '-') ?: Str::slug(class_basename($document));

        $content = self::toString($payload, $format);

        return response()->streamDownload(
            static function () use ($content): void {
                echo $content;
            },
            sprintf('%s.%s', $fileName, strtolower($format)),
            ['Content-Type' => self::contentType($format)],
        );
    }

    public static function toString(array $payload, string $format): string
    {
        $format = strtolower($format);

        if (! in_array($format, self::SUPPORTED_FORMATS, true)) {
            throw new InvalidArgumentException("Unsupported export format [{$format}]");
        }

        return match ($format) {
            'pdf' => self::toPseudoPdf($payload),
            'csv' => self::toCsv($payload),
            'xml' => self::toXml($payload),
            default => throw new InvalidArgumentException("Unsupported export format [{$format}]")
        };
    }

    private static function toPseudoPdf(array $payload): string
    {
        $flattened = self::flattenPayload($payload);

        $pdf = Pdf::loadView('documents.export', [
            'payload' => $payload,
            'flattened' => $flattened,
        ]);

        return $pdf->output();
    }

    private static function toCsv(array $payload): string
    {
        $flat = self::flattenPayload($payload);

        $lines = ['"Field","Value"'];

        foreach ($flat as $key => $value) {
            $lines[] = sprintf('"%s","%s"', str_replace('"', '""', (string) $key), str_replace('"', '""', (string) $value));
        }

        return implode("\n", $lines);
    }

    private static function toXml(array $payload): string
    {
        $root = new SimpleXMLElement('<document />');
        self::appendToXml($root, $payload);

        return $root->asXML() ?: '';
    }

    private static function appendToXml(SimpleXMLElement $node, array $payload): void
    {
        foreach ($payload as $key => $value) {
            $element = is_numeric($key) ? 'item' : (string) $key;

            if (is_array($value)) {
                $child = $node->addChild($element);
                self::appendToXml($child, $value);

                if (is_numeric($key)) {
                    $child->addAttribute('index', (string) $key);
                }

                continue;
            }

            $node->addChild($element, htmlspecialchars((string) $value));
        }
    }

    private static function flattenPayload(array $payload, string $prefix = ''): array
    {
        $flat = [];

        foreach ($payload as $key => $value) {
            $composedKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $flat += self::flattenPayload($value, $composedKey);

                continue;
            }

            $flat[$composedKey] = (string) $value;
        }

        return $flat;
    }

    private static function contentType(string $format): string
    {
        return match (strtolower($format)) {
            'pdf' => 'application/pdf',
            'csv' => 'text/csv',
            'xml' => 'application/xml',
            default => 'application/octet-stream',
        };
    }
}
