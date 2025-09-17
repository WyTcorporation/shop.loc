@php
    $heading = __('Оплату отримано');
    $introLines = [
        __('Замовлення №:number успішно оплачене.', ['number' => $order->number]),
        __('Ми готуємо його до відправлення та повідомимо про наступні кроки.'),
    ];
    $buttonUrl = config('app.url');
    $buttonLabel = __('До магазину');
    $total = (float) ($order->total ?? 0);
    $timezone = config('app.timezone', 'UTC');
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
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Сума до сплати') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#111;font-weight:600;">{{ \App\Support\OrderMailFormatter::money($order, $total) }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #f1f1f1;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Статус') }}</td>
            <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f1f1;font-size:14px;color:#0b7a29;font-weight:700;">{{ __('Оплачено') }}</td>
        </tr>
        @if($order->paid_at)
            <tr>
                <td style="padding:10px 0;color:#666;font-size:12px;text-transform:uppercase;letter-spacing:0.03em;">{{ __('Дата оплати') }}</td>
                <td align="right" style="padding:10px 0;font-size:14px;color:#111;font-weight:600;">{{ $order->paid_at->timezone($timezone)->format('d.m.Y H:i') }}</td>
            </tr>
        @endif
        </tbody>
    </table>
</x-emails.orders.layout>
