@php
    use App\Models\Order;
    /** @var Order|null $record */
    $record   = $getRecord();
    $items    = $record?->items ?? collect();
    $count    = (int) $items->sum('qty');
    $subtotal = (float) $items->sum(fn($i) => (float)$i->price * (int)$i->qty);
    $total    = (float) ($record?->total ?? $subtotal);
@endphp

<div class="rounded-xl border p-4 space-y-2" wire:poll.1500ms>
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">Positions</div>
        <div class="font-medium">{{ $items->count() }}</div>
    </div>

    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">Subtotal</div>
        <div class="font-semibold">₴ {{ number_format($subtotal, 2) }}</div>
    </div>

    <div class="border-t my-2"></div>

    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">Total (order)</div>
        <div class="text-lg font-bold">₴ {{ number_format((float)($record->total ?? 0), 2) }}</div>
    </div>
</div>
