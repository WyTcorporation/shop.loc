@php
    $subtotal = (float) ($order->subtotal ?? $order->total ?? 0);
    $total = (float) ($order->total ?? 0);
    $discountTotal = max(0, (float) ($order->discount_total ?? (($order->coupon_discount ?? 0) + ($order->loyalty_points_value ?? 0))));
    $couponCode = $order->coupon_code;
    $loyaltyPointsUsed = (int) ($order->loyalty_points_used ?? 0);
    $loyaltyPointsValue = max(0, (float) ($order->loyalty_points_value ?? 0));
@endphp

@component('mail::message')
    # Статус замовлення оновлено

    Замовлення **#{{ $order->number }}**

    @isset($fromStatus)
        **Було:** {{ $fromStatus }}
    @endisset

    **Стало:** {{ $toStatus }}

    @component('mail::panel')
        Сума товарів: **{{ \App\Support\OrderMailFormatter::money($order, $subtotal) }}**
        @if(!empty($couponCode))
        Купон: **{{ $couponCode }}**
        @endif
        @if($discountTotal > 0)
        Знижка: **−{{ \App\Support\OrderMailFormatter::money($order, $discountTotal) }}**
        @endif
        @if($loyaltyPointsUsed > 0)
        Використані бали: **{{ number_format($loyaltyPointsUsed, 0, ',', ' ') }}@if($loyaltyPointsValue > 0) (−{{ \App\Support\OrderMailFormatter::money($order, $loyaltyPointsValue) }})@endif**
        @endif
        До сплати: **{{ \App\Support\OrderMailFormatter::money($order, $total) }}**
        Статус: **{{ $toStatus }}**
        Дата: {{ $order->updated_at->format('Y-m-d H:i') }}
    @endcomponent

    Дякуємо за покупку!
    Команда {{ config('app.name') }}
@endcomponent
