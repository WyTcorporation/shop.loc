@php
    $appName = config('app.name', 'Shop');
    $heading = __('shop.auth.reset.changed_subject', ['app' => $appName]);
    $introLines = [
        __('shop.auth.greeting', ['name' => $user->name]),
        __('shop.auth.reset.changed_intro', ['app' => $appName]),
    ];
@endphp

<x-emails.auth.layout
    :title="__('shop.auth.reset.changed_title')"
    :heading="$heading"
    :intro-lines="$introLines"
>
    <p style="margin:0 0 16px 0;color:#444;font-size:14px;">
        {{ __('shop.auth.reset.changed_warning') }}
    </p>

    <p style="margin:0;color:#666;font-size:13px;">
        {{ __('shop.auth.reset.signature', ['app' => $appName]) }}
    </p>
</x-emails.auth.layout>
