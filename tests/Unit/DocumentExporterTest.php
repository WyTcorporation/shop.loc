<?php

use App\Models\Invoice;
use App\Services\Documents\DocumentExporter;

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
