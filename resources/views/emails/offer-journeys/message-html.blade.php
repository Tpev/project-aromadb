<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light only"><meta name="supported-color-schemes" content="light"><title>{{ $messageSubject }}</title></head>
@php
    $emailStyle = $emailStyle ?? ['primary_color' => '#647a0b', 'background_color' => '#f7f8f3', 'content_background' => '#ffffff'];
    $primaryColor = $emailStyle['primary_color'] ?? '#647a0b';
    $backgroundColor = $emailStyle['background_color'] ?? '#f7f8f3';
    $contentBackground = $emailStyle['content_background'] ?? '#ffffff';
@endphp
<body style="margin:0;background:{{ $backgroundColor }};color:#1f2937;font-family:Arial,sans-serif;">
@if(!empty($preheader ?? null))<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader }}</div>@endif
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:{{ $backgroundColor }};padding:24px 12px;">
    <tr><td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:{{ $contentBackground }};border:1px solid #e5e7eb;">
            <tr><td style="padding:20px 28px;border-bottom:3px solid {{ $primaryColor }};font-size:18px;font-weight:bold;color:{{ $primaryColor }};">
                @if(!empty($logoUrl ?? null))<img src="{{ $logoUrl }}" alt="{{ $therapistName }}" style="display:block;max-width:180px;max-height:58px;width:auto;height:auto;border:0;">@else{{ $therapistName }}@endif
            </td></tr>
            @if(isset($renderedBlocksHtml))
                {!! $renderedBlocksHtml !!}
            @else
                <tr><td style="padding:28px;font-size:16px;line-height:1.65;">{!! nl2br(e($body)) !!}</td></tr>
            @endif
            <tr><td style="padding:20px 28px;background:#f9fafb;font-size:12px;line-height:1.5;color:#6b7280;">
                Ce message vous est adressé par {{ $therapistName }} via Olithea.
                @if($category === 'marketing')<br><a href="{{ $unsubscribeUrl }}" style="color:{{ $primaryColor }};">Se désinscrire de ces suivis</a>@endif
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
