@php
    $items = $order->items ?? collect();
    $total = (float)($order->total ?? $items->sum(fn($i)=> (float)$i->price * (int)$i->qty));
@endphp
    <!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Підтвердження замовлення {{ $order->number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<body style="margin:0;padding:0;background:#f6f7f9;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7f9;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden">
                <tr>
                    <td style="padding:24px 24px 0 24px;">
                        <h1 style="margin:0 0 8px 0;font-size:20px;color:#111">Дякуємо за замовлення!</h1>
                        <p style="margin:0 0 16px 0;color:#444;font-size:14px">
                            Замовлення <b>{{ $order->number }}</b> оформлено.
                            Ми надішлемо оновлення на <b>{{ $order->email }}</b>.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 24px 16px 24px">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse">
                            <thead>
                            <tr>
                                <th align="left" style="padding:8px 0;border-bottom:1px solid #eee;font-size:12px;color:#666">Товар</th>
                                <th align="center" style="padding:8px 0;border-bottom:1px solid #eee;font-size:12px;color:#666">К-сть</th>
                                <th align="right" style="padding:8px 0;border-bottom:1px solid #eee;font-size:12px;color:#666">Ціна</th>
                                <th align="right" style="padding:8px 0;border-bottom:1px solid #eee;font-size:12px;color:#666">Сума</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $it)
                                @php
                                    $name  = $it->name ?? $it->product->name ?? ('#'.$it->product_id);
                                    $price = (float)($it->price ?? $it->product->price ?? 0);
                                    $qty   = (int)($it->qty ?? 1);
                                    $sum   = $price * $qty;
                                    $img   = $it->preview_url
                                           ?? optional($it->product?->images?->firstWhere('is_primary', true))->url
                                           ?? optional($it->product?->images?->first())->url
                                           ?? $it->product?->preview_url;
                                @endphp
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #f1f1f1">
                                        <div style="display:flex;gap:10px;align-items:center">
                                            @if($img)
                                                <img src="{{ $img }}" alt="" width="48" height="48" style="display:block;border:1px solid #eee;border-radius:8px;object-fit:cover">
                                            @endif
                                            <div style="font-size:14px;color:#111">{{ $name }}</div>
                                        </div>
                                    </td>
                                    <td align="center" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111">{{ $qty }}</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111">{{ number_format($price,2,',',' ') }} грн</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111">{{ number_format($sum,2,',',' ') }} грн</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="3" align="right" style="padding:14px 0;font-weight:600;color:#111">Разом</td>
                                <td align="right" style="padding:14px 0;font-weight:700;color:#111">{{ number_format($total,2,',',' ') }} грн</td>
                            </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 24px 24px 24px;color:#666;font-size:12px">
                        Якщо виникли питання — просто відповідайте на цей лист.
                    </td>
                </tr>
            </table>

            <div style="color:#999;font-size:11px;margin-top:10px">
                © {{ date('Y') }} Shop
            </div>
        </td>
    </tr>
</table>
</body>
</html>
