@props([
    'title' => null,
    'heading' => null,
    'introLines' => [],
    'buttonUrl' => null,
    'buttonLabel' => null,
    'footerNote' => null,
])

@php
    $introLines = is_string($introLines) ? [$introLines] : (array) $introLines;
    $footerNote ??= __('shop.common.footer_note');
    $htmlLang = str_replace('_', '-', app()->getLocale());
@endphp

<!doctype html>
<html lang="{{ $htmlLang }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<body style="margin:0;padding:0;background:#f6f7f9;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7f9;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden">
                <tr>
                    <td style="padding:24px 24px 0 24px;">
                        @if($heading)
                            <h1 style="margin:0 0 8px 0;font-size:20px;color:#111">{{ $heading }}</h1>
                        @endif
                        @foreach($introLines as $line)
                            <p style="margin:0 0 12px 0;color:#444;font-size:14px">{!! nl2br(e($line)) !!}</p>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 24px 24px">
                        {{ $slot }}
                    </td>
                </tr>
                @if($buttonUrl && $buttonLabel)
                    <tr>
                        <td style="padding:0 24px 32px 24px;">
                            <a href="{{ $buttonUrl }}" style="display:inline-block;background:#111;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;">
                                {{ $buttonLabel }}
                            </a>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:0 24px 24px 24px;color:#666;font-size:12px">
                        {{ $footerNote }}
                    </td>
                </tr>
            </table>
            <div style="color:#999;font-size:11px;margin-top:10px">
                © {{ now()->year }} {{ config('app.name', 'Shop') }}
            </div>
        </td>
    </tr>
</table>
</body>
</html>
