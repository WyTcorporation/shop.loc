@component('mail::message')
    # Дякуємо за замовлення!

    Ваше замовлення **#{{ $order->number }}** успішно створено.

    @component('mail::panel')
        **Сума:** {{ number_format((float) $order->total, 2) }}
        **Статус:** {{ $order->status }}
        **Створено:** {{ $order->created_at->format('Y-m-d H:i') }}
    @endcomponent

    ## Товари
    @php
        $rows = "| Товар | К-сть | Ціна | Сума |\n|:------|:----:|-----:|-----:|\n";
        foreach ($order->items as $it) {
          $name = $it->product?->name ?? ("ID #".$it->product_id);
          $sum = (float) $it->qty * (float) $it->price;
          $rows .= "| {$name} | {$it->qty} | ".number_format((float)$it->price, 2)." | ".number_format($sum, 2)." |\n";
        }
    @endphp
    @component('mail::table')
        {!! $rows !!}
    @endcomponent

    ## Доставка
    **Імʼя:** {{ data_get($order->shipping_address, 'name', '—') }}
    **Місто:** {{ data_get($order->shipping_address, 'city', '—') }}
    **Адреса:** {{ data_get($order->shipping_address, 'addr', '—') }}

    @isset($order->note)
        > Примітка: {{ $order->note }}
    @endisset

    Якщо виникнуть питання — просто відповідайте на цей лист.

    З повагою,
    {{ config('app.name') }}
@endcomponent
