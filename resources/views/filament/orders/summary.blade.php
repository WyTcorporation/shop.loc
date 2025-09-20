@php
    use App\Models\Order;
    /** @var Order|null $record */
    $record   = $getRecord();
    $items    = $record?->items ?? collect();
    $count    = (int) $items->sum('qty');
    $subtotal = (float) $items->sum(fn ($i) => (float) $i->price * (int) $i->qty);
    $total    = (float) ($record?->total ?? $subtotal);
    $currency = $record?->currency;
@endphp

<div class="rounded-xl border p-4 space-y-2" wire:poll.1500ms>
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">{{ __('shop.orders.summary.positions') }}</div>
        <div class="font-medium">{{ $items->count() }}</div>
    </div>

    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">{{ __('shop.orders.summary.subtotal') }}</div>
        <div class="font-semibold">{{ formatCurrency($subtotal, $currency) }}</div>
    </div>

    <div class="border-t my-2"></div>

    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">{{ __('shop.orders.summary.total_order') }}</div>
        <div class="text-lg font-bold">{{ formatCurrency($total, $currency) }}</div>
    </div>
</div>
