@component('mail::message')
    # It's here!

    Your order **{{ $order->number }}** has been **delivered**.

    @component('mail::panel')
        Total: **{{ number_format((float)$order->total, 2) }}**
        Status: **{{ $order->status }}**
    @endcomponent

    @component('mail::button', ['url' => config('app.url')])
        View order
    @endcomponent

    Cheers,<br>
    {{ config('app.name') }}
@endcomponent
