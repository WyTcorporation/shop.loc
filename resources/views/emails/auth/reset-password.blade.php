@php
    $appName = config('app.name', 'Shop');
    $heading = __('Відновлення доступу до :app', ['app' => $appName]);
    $introLines = [
        __('Привіт, :name!', ['name' => $user->name]),
        __('Ви отримали цей лист, бо ми отримали запит на скидання пароля для вашого облікового запису в :app.', ['app' => $appName]),
    ];
@endphp

<x-emails.auth.layout
    :title="__('Скидання пароля')"
    :heading="$heading"
    :intro-lines="$introLines"
    :button-url="$resetUrl"
    :button-label="__('Скинути пароль')"
>
    <p style="margin:0 0 16px 0;color:#444;font-size:14px;">
        {{ __('Кнопка не працює? Скопіюйте та вставте це посилання у свій браузер:') }}
        <a href="{{ $resetUrl }}" style="color:#2563eb;text-decoration:none;">{{ $displayUrl }}</a>
    </p>

    <p style="margin:0;color:#666;font-size:13px;">
        {{ __('Якщо ви не запитували скидання пароля, просто проігноруйте цей лист.') }}
    </p>
</x-emails.auth.layout>
