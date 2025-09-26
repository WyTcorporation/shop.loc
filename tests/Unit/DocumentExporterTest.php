<?php

use App\Models\Invoice;
use App\Services\Documents\DocumentExporter;
use Tests\TestCase;

uses(TestCase::class);

it('exports invoice metadata to csv and xml', function () {
    $invoice = new Invoice();
    $invoice->number = 'INV-123';
    $invoice->currency = 'USD';
    $invoice->subtotal = '100.00';
    $invoice->tax_total = '20.00';
    $invoice->total = '120.00';
    $invoice->metadata = [
        'custom_note' => 'Handle with care',
        'tags' => ['priority', 'fragile'],
    ];
    $invoice->setRelation('order', (object) [
        'number' => 'ORD-456',
        'currency' => 'USD',
        'email' => 'customer@example.test',
        'user' => null,
    ]);

    $payload = $invoice->toExportArray();

    expect($payload['metadata'])
        ->toBe([
            'custom_note' => 'Handle with care',
            'tags' => ['priority', 'fragile'],
        ]);

    $csv = DocumentExporter::toString($payload, 'csv');

    expect($csv)
        ->toContain('"metadata.custom_note","Handle with care"')
        ->toContain('"metadata.tags.0","priority"')
        ->toContain('"metadata.tags.1","fragile"');

    $xml = DocumentExporter::toString($payload, 'xml');

    expect($xml)
        ->toContain('<metadata>')
        ->toContain('<custom_note>Handle with care</custom_note>')
        ->toContain('<tags>')
        ->toContain('<item>priority</item>')
        ->toContain('<item>fragile</item>');
});

it('downloads invoice export as a pdf document', function () {
    $invoice = new Invoice();
    $invoice->number = 'INV-789';
    $invoice->currency = 'EUR';
    $invoice->subtotal = '50.00';
    $invoice->tax_total = '10.00';
    $invoice->total = '60.00';
    $invoice->setRelation('order', (object) [
        'number' => 'ORD-789',
        'currency' => 'EUR',
        'email' => 'client@example.test',
        'user' => null,
    ]);

    $response = DocumentExporter::download($invoice, 'pdf', 'invoice');

    expect($response->headers->get('content-type'))->toBe('application/pdf');

    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect($content)
        ->not->toBeEmpty()
        ->toStartWith('%PDF-');
});
