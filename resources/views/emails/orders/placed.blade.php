@php
    $items = ($order->items ?? collect())->map(function ($item) {
        $name = $item->name ?? $item->product->name ?? ('#' . $item->product_id);
        $price = (float) ($item->price ?? $item->product->price ?? 0);
        $qty = max((int) ($item->qty ?? 1), 1);
        $img = $item->preview_url
            ?? optional($item->product?->images?->firstWhere('is_primary', true))->url
            ?? optional($item->product?->images?->first())->url
            ?? $item->product?->preview_url;

        return [
            'name' => $name,
            'price' => $price,
            'qty' => $qty,
            'sum' => $price * $qty,
            'img' => $img,
        ];
    });

    $total = (float) ($order->total ?? $items->sum('sum'));
    $heading = __('Дякуємо за замовлення!');
    $introLines = [
        __('Замовлення №:number оформлено.', ['number' => $order->number]),
        __('Ми надішлемо оновлення на :email.', ['email' => $order->email]),
    ];
@endphp

<x-emails.orders.layout :order="$order" :heading="$heading" :intro-lines="$introLines">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse">
        <thead>
        <tr>
            <th align="left" style="padding:8px 0;border-bottom:1px solid #eee;font-size:12px;color:#666">{{ __('Товар') }}</th>
            <th align="center" style="padding:8px 0;border-bottom:1px solid #eee;font-size:12px;color:#666">{{ __('К-сть') }}</th>
            <th align="right" style="padding:8px 0;border-bottom:1px solid #eee;font-size:12px;color:#666">{{ __('Ціна') }}</th>
            <th align="right" style="padding:8px 0;border-bottom:1px solid #eee;font-size:12px;color:#666">{{ __('Сума') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1">
                    <div style="display:flex;gap:10px;align-items:center">
                        @if($item['img'])
                            <img src="{{ $item['img'] }}" alt="" width="48" height="48" style="display:block;border:1px solid #eee;border-radius:8px;object-fit:cover">
                        @endif
                        <div style="font-size:14px;color:#111">{{ $item['name'] }}</div>
                    </div>
                </td>
                <td align="center" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111">{{ $item['qty'] }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111">{{ \App\Support\OrderMailFormatter::money($order, $item['price']) }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111">{{ \App\Support\OrderMailFormatter::money($order, $item['sum']) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" align="right" style="padding:14px 0;font-weight:600;color:#111">{{ __('Разом') }}</td>
            <td align="right" style="padding:14px 0;font-weight:700;color:#111">{{ \App\Support\OrderMailFormatter::money($order, $total) }}</td>
        </tr>
        </tfoot>
    </table>
</x-emails.orders.layout>
