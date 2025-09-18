@php
    $appName = config('app.name', 'Shop');
    $heading = __('shop.auth.verify.subject', ['app' => $appName]);
    $introLines = [
        __('shop.auth.greeting', ['name' => $user->name]),
        __('shop.auth.verify.intro', ['app' => $appName]),
    ];
@endphp

<x-emails.auth.layout
    :title="__('shop.auth.verify.title')"
    :heading="$heading"
    :intro-lines="$introLines"
    :button-url="$verificationUrl"
    :button-label="__('shop.auth.verify.button')"
>
    @if ($verificationUrl)
        <p style="margin:0 0 16px 0;color:#444;font-size:14px;">
            {{ __('shop.auth.reset.link_help') }}
            <a href="{{ $verificationUrl }}" style="color:#2563eb;text-decoration:none;">{{ $displayUrl ?? $verificationUrl }}</a>
        </p>
    @endif

    <p style="margin:0;color:#666;font-size:13px;">
        {{ __('shop.auth.verify.ignore') }}
    </p>
</x-emails.auth.layout>
