@component('mail::message')
    # Payment received

    Your order **{{ $order->number }}** is now **paid**.

    @component('mail::panel')
        Total: **{{ number_format((float)$order->total, 2) }}**
        Status: **{{ $order->status }}**
    @endcomponent

    @component('mail::button', ['url' => config('app.url')])
        Go to shop
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
