@php
    $appName = config('app.name', 'Shop');
    $heading = __('Підтвердіть електронну адресу для :app', ['app' => $appName]);
    $introLines = [
        __('Привіт, :name!', ['name' => $user->name]),
        __('Щоб активувати свій обліковий запис у :app, підтвердіть електронну адресу протягом наступної години.', ['app' => $appName]),
    ];
@endphp

<x-emails.auth.layout
    :title="__('Підтвердіть електронну адресу')"
    :heading="$heading"
    :intro-lines="$introLines"
    :button-url="$verificationUrl"
    :button-label="__('Підтвердити електронну адресу')"
>
    @if ($verificationUrl)
        <p style="margin:0 0 16px 0;color:#444;font-size:14px;">
            {{ __('Кнопка не працює? Скопіюйте та вставте це посилання у свій браузер:') }}
            <a href="{{ $verificationUrl }}" style="color:#2563eb;text-decoration:none;">{{ $displayUrl ?? $verificationUrl }}</a>
        </p>
    @endif

    <p style="margin:0;color:#666;font-size:13px;">
        {{ __('Якщо ви не створювали обліковий запис, просто проігноруйте цей лист.') }}
    </p>
</x-emails.auth.layout>
