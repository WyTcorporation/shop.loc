@component('mail::message')
    # Статус замовлення оновлено

    Замовлення **#{{ $order->number }}**

    @isset($fromStatus)
        **Було:** {{ $fromStatus }}
    @endisset

    **Стало:** {{ $toStatus }}

    @component('mail::panel')
        Сума: **{{ number_format((float)$order->total, 2) }}**
        Статус: **{{ $toStatus }}**
        Дата: {{ $order->updated_at->format('Y-m-d H:i') }}
    @endcomponent

    Дякуємо за покупку!
    Команда {{ config('app.name') }}
@endcomponent
