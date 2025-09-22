@php
    $subtotal = (float) ($order->subtotal ?? $order->total ?? 0);
    $total = (float) ($order->total ?? 0);
    $discountTotal = max(0, (float) ($order->discount_total ?? (($order->coupon_discount ?? 0) + ($order->loyalty_points_value ?? 0))));
    $couponCode = $order->coupon_code;
    $loyaltyPointsUsed = (int) ($order->loyalty_points_used ?? 0);
    $loyaltyPointsValue = max(0, (float) ($order->loyalty_points_value ?? 0));
@endphp

@component('mail::message')
# {{ __('shop.orders.status_updated.heading') }}

{{ __('shop.orders.status_updated.order_intro', ['number' => $order->number]) }}

@isset($fromStatus)
**{{ __('shop.orders.status_updated.labels.from') }}:** {{ $fromStatus }}
@endisset

**{{ __('shop.orders.status_updated.labels.to') }}:** {{ $toStatus }}

@component('mail::panel')
{{ __('shop.orders.status_updated.labels.subtotal') }}: **{{ \App\Support\OrderMailFormatter::money($order, $subtotal) }}**
@if(! empty($couponCode))
{{ __('shop.orders.status_updated.labels.coupon') }}: **{{ $couponCode }}**
@endif
@if($discountTotal > 0)
{{ __('shop.orders.status_updated.labels.discount') }}: **−{{ \App\Support\OrderMailFormatter::money($order, $discountTotal) }}**
@endif
@if($loyaltyPointsUsed > 0)
{{ __('shop.orders.status_updated.labels.loyalty_points') }}: **{{ number_format($loyaltyPointsUsed, 0, ',', ' ') }}@if($loyaltyPointsValue > 0) (−{{ \App\Support\OrderMailFormatter::money($order, $loyaltyPointsValue) }})@endif**
@endif
{{ __('shop.orders.status_updated.labels.total') }}: **{{ \App\Support\OrderMailFormatter::money($order, $total) }}**
{{ __('shop.orders.status_updated.labels.status') }}: **{{ $toStatus }}**
{{ __('shop.orders.status_updated.labels.date') }}: {{ $order->updated_at->format('Y-m-d H:i') }}
@endcomponent

{{ __('shop.orders.status_updated.thanks') }}
{{ __('shop.orders.status_updated.team_signature', ['app' => config('app.name')]) }}
@endcomponent
