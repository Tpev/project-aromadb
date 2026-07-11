<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $messageSubject }}</title></head>
<body style="margin:0;background:#f7f8f3;color:#1f2937;font-family:Arial,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f8f3;padding:24px 12px;">
    <tr><td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e5e7eb;">
            <tr><td style="padding:20px 28px;border-bottom:3px solid #647a0b;font-size:18px;font-weight:bold;color:#647a0b;">{{ $therapistName }}</td></tr>
            <tr><td style="padding:28px;font-size:16px;line-height:1.65;">{!! nl2br(e($body)) !!}</td></tr>
            <tr><td style="padding:20px 28px;background:#f9fafb;font-size:12px;line-height:1.5;color:#6b7280;">
                Ce message vous est adressé par {{ $therapistName }} via Olithea.
                @if($category === 'marketing')<br><a href="{{ $unsubscribeUrl }}" style="color:#647a0b;">Se désinscrire de ces suivis</a>@endif
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
