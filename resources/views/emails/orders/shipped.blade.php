@php
    $heading = __('shop.orders.shipped.subject');
    $introLines = [
        __('shop.orders.shipped.intro', ['number' => $order->number]),
        __('shop.orders.shipped.next'),
    ];
    $buttonUrl = config('app.url');
    $buttonLabel = __('shop.orders.shipped.button');
    $total = (float) ($order->total ?? 0);
    $subtotal = (float) ($order->subtotal ?? $total);
    $discountTotal = max(0, (float) ($order->discount_total ?? (($order->coupon_discount ?? 0) + ($order->loyalty_points_value ?? 0))));
    $couponCode = $order->coupon_code;
    $loyaltyPointsUsed = (int) ($order->loyalty_points_used ?? 0);
    $loyaltyPointsValue = max(0, (float) ($order->loyalty_points_value ?? 0));
    $timezone = config('app.timezone', 'UTC');
    $trackingNumber = $order->shipment?->tracking_number ?? null;
    $deliveryMethod = $order->shipment?->delivery_method ?? null;
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
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.order_number') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ $order->number }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.items_total') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ \App\Support\OrderMailFormatter::money($order, $subtotal) }}</td>
        </tr>
        @if(!empty($couponCode))
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.coupon') }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ $couponCode }}</td>
            </tr>
        @endif
        @if($discountTotal > 0)
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.discount') }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#d20000;font-weight:600;">−{{ \App\Support\OrderMailFormatter::money($order, $discountTotal) }}</td>
            </tr>
        @endif
        @if($loyaltyPointsUsed > 0)
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.used_points') }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">
                    {{ number_format($loyaltyPointsUsed, 0, ',', ' ') }}
                    @if($loyaltyPointsValue > 0)
                        <span style="color:#666;font-weight:600;">(−{{ \App\Support\OrderMailFormatter::money($order, $loyaltyPointsValue) }})</span>
                    @endif
                </td>
            </tr>
        @endif
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.order_total') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ \App\Support\OrderMailFormatter::money($order, $total) }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.status') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#0052b4;font-weight:700;">{{ __('shop.common.shipped') }}</td>
        </tr>
        @if(! empty($deliveryMethod))
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.delivery_method') }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ $deliveryMethod }}</td>
            </tr>
        @endif
        @if(! empty($trackingNumber))
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.tracking_number') }}</td>
                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ $trackingNumber }}</td>
            </tr>
        @endif
        @if($order->shipped_at)
            <tr>
                <td style="padding:10px 0;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('shop.common.shipped_at') }}</td>
                <td align="right" style="padding:10px 0;font-size:14px;color:#111;font-weight:600;">{{ $order->shipped_at->timezone($timezone)->format('d.m.Y H:i') }}</td>
            </tr>
        @endif
        </tbody>
    </table>
</x-emails.orders.layout>
