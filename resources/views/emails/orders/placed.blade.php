@component('mail::message')
    # Thanks for your order {{ $order->number }}

    Total: **${{ number_format($order->total, 2) }}**

    @component('mail::button', ['url' => config('app.url')])
        Go to shop
    @endcomponent

    Thanks,\
    {{ config('app.name') }}
@endcomponent
