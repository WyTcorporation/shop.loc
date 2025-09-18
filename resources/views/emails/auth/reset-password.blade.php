@php
    $appName = config('app.name', 'Shop');
    $heading = __('shop.auth.reset.heading', ['app' => $appName]);
    $introLines = [
        __('shop.auth.greeting', ['name' => $user->name]),
        __('shop.auth.reset.intro', ['app' => $appName]),
    ];
@endphp

<x-emails.auth.layout
    :title="__('shop.auth.reset.title')"
    :heading="$heading"
    :intro-lines="$introLines"
    :button-url="$resetUrl"
    :button-label="__('shop.auth.reset.button')"
>
    <p style="margin:0 0 16px 0;color:#444;font-size:14px;">
        {{ __('shop.auth.reset.link_help') }}
        <a href="{{ $resetUrl }}" style="color:#2563eb;text-decoration:none;">{{ $displayUrl }}</a>
    </p>

    <p style="margin:0;color:#666;font-size:13px;">
        {{ __('shop.auth.reset.ignore') }}
    </p>
</x-emails.auth.layout>
