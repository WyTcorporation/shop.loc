@php
    $heading = __('Замовлення доставлено');
    $introLines = [
        __('Замовлення №:number успішно доставлене.', ['number' => $order->number]),
        __('Сподіваємося, що вам сподобалися покупки. Дякуємо, що обрали :app!', ['app' => config('app.name')]),
    ];
    $buttonUrl = config('app.url');
    $buttonLabel = __('Переглянути замовлення');

    $total = (float) ($order->total ?? 0);
    $subtotal = (float) ($order->subtotal ?? $total);
    $discount = max(0, (float) ($order->discount_total ?? (($order->coupon_discount ?? 0) + ($order->loyalty_points_value ?? 0))));
    $couponCode = $order->coupon_code;
    $loyaltyPointsUsed = (int) ($order->loyalty_points_used ?? 0);
    $loyaltyPointsValue = max(0, (float) ($order->loyalty_points_value ?? 0));
    $timezone = config('app.timezone', 'UTC');
    $deliveredAt = $order->shipment?->delivered_at;
@endphp

<x-emails.orders.layout
    :order="$order"
    :heading="$heading"
    :intro-lines="$introLines"
    :button-url="$buttonUrl"
    :button-label="$buttonLabel"
>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse">
        <tbody>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Номер замовлення') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ $order->number }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Сума товарів') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ \App\Support\OrderMailFormatter::money($order, $subtotal) }}</td>
        </tr>
        @if(!empty($couponCode))
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Купон') }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ $couponCode }}</td>
            </tr>
        @endif
        @if($discount > 0)
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Знижка') }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#d20000;font-weight:600;">−{{ \App\Support\OrderMailFormatter::money($order, $discount) }}</td>
            </tr>
        @endif
        @if($loyaltyPointsUsed > 0)
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Використані бали') }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">
                    {{ number_format($loyaltyPointsUsed, 0, ',', ' ') }}
                    @if($loyaltyPointsValue > 0)
                        <span style="color:#666;font-weight:600;">(−{{ \App\Support\OrderMailFormatter::money($order, $loyaltyPointsValue) }})</span>
                    @endif
                </td>
            </tr>
        @endif
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('До сплати') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:700;">{{ \App\Support\OrderMailFormatter::money($order, $total) }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Статус') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#0b7a29;font-weight:700;">{{ __('Доставлено') }}</td>
        </tr>
        @if($deliveredAt)
            <tr>
                <td style="padding:10px 0;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Дата доставки') }}</td>
                <td align="right" style="padding:10px 0;font-size:14px;color:#111;font-weight:600;">{{ $deliveredAt->timezone($timezone)->format('d.m.Y H:i') }}</td>
            </tr>
        @endif
        </tbody>
    </table>
</x-emails.orders.layout>
