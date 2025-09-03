@component('mail::message')
    # Good news!

    Your order **{{ $order->number }}** has been **shipped**.

    @component('mail::panel')
        Total: **{{ number_format((float)$order->total, 2) }}**
        Status: **{{ $order->status }}**
    @endcomponent

    @component('mail::button', ['url' => config('app.url')])
        Track order
    @endcomponent

    Cheers,<br>
    {{ config('app.name') }}
@endcomponent
