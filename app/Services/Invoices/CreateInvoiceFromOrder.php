<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreateInvoiceFromOrder
{
    public function handle(Order $order): Invoice
    {
        if (! $order->exists) {
            throw new InvalidArgumentException('Cannot create an invoice for an unsaved order.');
        }

        return Invoice::firstOrCreate(
            ['order_id' => $order->getKey()],
            $this->buildPayload($order),
        );
    }

    public function __invoke(Order $order): Invoice
    {
        return $this->handle($order);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildPayload(Order $order): array
    {
        $subtotal = $this->toMoney($order->subtotal);
        $discount = $this->toMoney($order->discount_total);
        $total = $this->toMoney($order->total);

        return [
            'number' => $this->makeInvoiceNumber($order),
            'issued_at' => $order->paid_at ?? now(),
            'status' => 'paid',
            'currency' => $order->currency,
            'subtotal' => $subtotal,
            'tax_total' => $this->calculateTaxTotal($subtotal, $discount, $total),
            'total' => $total,
        ];
    }

    protected function makeInvoiceNumber(Order $order): string
    {
        $base = Str::of((string) ($order->number ?? $order->getKey()))
            ->after('ORD-')
            ->replaceMatches('/[^A-Za-z0-9]/', '')
            ->upper();

        if ($base->isEmpty()) {
            $base = Str::of((string) $order->getKey())->padLeft(6, '0');
        }

        return 'INV-' . $base;
    }

    protected function calculateTaxTotal(float $subtotal, float $discount, float $total): float
    {
        $taxTotal = $total - ($subtotal - $discount);

        return round(max($taxTotal, 0), 2);
    }

    protected function toMoney(mixed $value): float
    {
        return round((float) ($value ?? 0), 2);
    }
}
