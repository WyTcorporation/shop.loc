@php
    $appName = config('app.name', 'Shop');
    $heading = __('Пароль до :app змінено', ['app' => $appName]);
    $introLines = [
        __('Привіт, :name!', ['name' => $user->name]),
        __('Ми щойно оновили пароль до вашого облікового запису в :app.', ['app' => $appName]),
    ];
@endphp

<x-emails.auth.layout
    :title="__('Пароль змінено')"
    :heading="$heading"
    :intro-lines="$introLines"
>
    <p style="margin:0 0 16px 0;color:#444;font-size:14px;">
        {{ __('Якщо ви не змінювали пароль, негайно звʼяжіться з нашою службою підтримки або скиньте пароль повторно, щоб захистити обліковий запис.') }}
    </p>

    <p style="margin:0;color:#666;font-size:13px;">
        {{ __('З повагою, команда :app.', ['app' => $appName]) }}
    </p>
</x-emails.auth.layout>
