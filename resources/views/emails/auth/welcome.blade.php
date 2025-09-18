@php
    $appName = config('app.name', 'Shop');
    $heading = __('Ласкаво просимо до :app!', ['app' => $appName]);
    $introLines = [
        __('Привіт, :name!', ['name' => $user->name]),
        __('Дякуємо за реєстрацію у :app. Щоб завершити створення облікового запису, підтвердіть свою електронну адресу.', ['app' => $appName]),
    ];
@endphp

<x-emails.auth.layout
    :title="__('Ласкаво просимо до :app', ['app' => $appName])"
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
