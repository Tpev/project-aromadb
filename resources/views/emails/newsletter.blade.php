{{-- resources/views/emails/newsletter.blade.php --}}
@php
    // Récupérer les blocs depuis l'accessor ou directement le JSON
    $blocks = $newsletter->blocks ?? json_decode($newsletter->content_json ?? '[]', true) ?? [];

    $bgColor = $newsletter->background_color ?: '#ffffff';

    // Gestion des merge tags simples
    $renderMergeTags = function (?string $html, $client) {
        if (!$html) {
            return '';
        }

        $replacements = [
            '{{ client.first_name }}' => $client->first_name ?? '',
            '{{client.first_name}}'   => $client->first_name ?? '',
            '{{ client.last_name }}'  => $client->last_name ?? '',
            '{{client.last_name}}'    => $client->last_name ?? '',
            '{{ client.full_name }}'  => trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')),
            '{{client.full_name}}'    => trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    };

    // Map des polices "marketing" vers des stacks web safe
    $fontStack = function (?string $family) {
        switch ($family) {
            case 'Montserrat':
                return "'Montserrat', Arial, sans-serif";
            case 'Arial':
                return "Arial, sans-serif";
            case 'Georgia':
                return "Georgia, 'Times New Roman', serif";
            case 'Times New Roman':
                return "'Times New Roman', Times, serif";
            case 'Verdana':
                return "Verdana, Geneva, sans-serif";
            default:
                return "Arial, sans-serif";
        }
    };
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $newsletter->subject }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Petites bases de style email-safe --}}
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        img {
            border: 0;
            -ms-interpolation-mode: bicubic;
            display: block;
            max-width: 100%;
            height: auto;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f3f4f6;
        }

        .main {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        @media only screen and (max-width: 620px) {
            .main {
                border-radius: 0 !important;
            }

            .content {
                padding: 16px !important;
            }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6;">
    <center class="wrapper">
        {{-- Pré-header caché pour la boîte mail --}}
        @if(!empty($newsletter->preheader))
            <div style="display:none; font-size:1px; color:#f3f4f6; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
                {{ $newsletter->preheader }}
            </div>
        @endif

        <table role="presentation" width="100%">
            <tr>
                <td align="center" style="padding:24px 12px;">
                    <table role="presentation" class="main" width="100%" style="background-color: {{ $bgColor }};">
                        {{-- "Header" (facultatif) --}}
                        <tr>
                            <td style="padding:16px 20px 8px 20px; background-color:#f9fafb; border-bottom:1px solid #e5e7eb;">
                                <div style="font-family: Arial, sans-serif; font-size:11px; color:#6b7280;">
                                    De : <span style="font-weight:600; color:#111827;">
                                        {{ $newsletter->from_name }}
                                    </span>
                                </div>
                                <div style="font-family: Arial, sans-serif; font-size:11px; color:#6b7280; margin-top:2px;">
                                    À : {{ trim(($client->first_name ?? 'Prénom').' '.($client->last_name ?? 'Nom')) }}
                                </div>
                            </td>
                        </tr>

                        {{-- Contenu principal --}}
                        <tr>
                            <td class="content" style="padding:24px 24px 16px 24px; font-family: Arial, sans-serif;">
                                @foreach($blocks as $block)
                                    @php
                                        $type = $block['type'] ?? 'text';
                                    @endphp

                                    {{-- Bloc Titre + texte --}}
                                    @if($type === 'heading_text')
                                        @php
                                            $heading       = $block['heading'] ?? '';
                                            $headingSize   = $block['heading_size'] ?? '22px';
                                            $headingColor  = $block['heading_color'] ?? '#111111';
                                            $textSize      = $block['text_size'] ?? '14px';
                                            $textColor     = $block['text_color'] ?? '#333333';
                                            $fontFamily    = $fontStack($block['font_family'] ?? null);
                                            $align         = $block['text_align'] ?? 'left';
                                            $html          = $renderMergeTags($block['html'] ?? '', $client);
                                        @endphp

                                        @if(strlen(trim($heading)) > 0)
                                            <h1 style="margin:0 0 8px 0;
                                                       font-size:{{ $headingSize }};
                                                       color:{{ $headingColor }};
                                                       font-family:{{ $fontFamily }};
                                                       text-align:{{ $align }};
                                                       font-weight:600;">
                                                {{ $heading }}
                                            </h1>
                                        @endif

                                        @if(strlen(trim($html)) > 0)
                                            <div style="margin:0 0 16px 0;
                                                        font-size:{{ $textSize }};
                                                        color:{{ $textColor }};
                                                        font-family:{{ $fontFamily }};
                                                        text-align:{{ $align }};
                                                        line-height:1.5;">
                                                {!! $html !!}
                                            </div>
                                        @endif

                                    {{-- Bloc texte simple --}}
                                    @elseif($type === 'text')
                                        @php
                                            $textSize   = $block['text_size'] ?? '14px';
                                            $textColor  = $block['text_color'] ?? '#333333';
                                            $fontFamily = $fontStack($block['font_family'] ?? null);
                                            $align      = $block['text_align'] ?? 'left';
                                            $html       = $renderMergeTags($block['html'] ?? '', $client);
                                        @endphp

                                        @if(strlen(trim($html)) > 0)
                                            <div style="margin:0 0 16px 0;
                                                        font-size:{{ $textSize }};
                                                        color:{{ $textColor }};
                                                        font-family:{{ $fontFamily }};
                                                        text-align:{{ $align }};
                                                        line-height:1.5;">
                                                {!! $html !!}
                                            </div>
                                        @endif

                                    {{-- Bloc image --}}
                                    @elseif($type === 'image')
                                        @php
                                            $url = $block['url'] ?? '';
                                            $alt = $block['alt'] ?? '';
                                        @endphp

                                        @if(!empty($url))
                                            <div style="margin:8px 0 16px 0; text-align:center;">
                                                <img src="{{ $url }}" alt="{{ $alt }}" style="border-radius:6px; max-width:100%; height:auto;">
                                            </div>
                                        @endif

                                    {{-- Bloc bouton --}}
                                    @elseif($type === 'button')
                                        @php
                                            $label      = $block['label'] ?? 'En savoir plus';
                                            $url        = $block['url'] ?? '#';
                                            $fontSize   = $block['font_size'] ?? '14px';
                                            $textColor  = $block['text_color'] ?? '#ffffff';
                                            $btnBgColor = $block['background_color'] ?? '#647a0b';
                                        @endphp

                                        @if(!empty($url))
                                            <div style="margin:12px 0 20px 0; text-align:center;">
                                                <a href="{{ $url }}"
                                                   style="display:inline-block;
                                                          padding:10px 22px;
                                                          border-radius:999px;
                                                          text-decoration:none;
                                                          font-weight:600;
                                                          font-size:{{ $fontSize }};
                                                          color:{{ $textColor }};
                                                          background-color:{{ $btnBgColor }};">
                                                    {{ $label }}
                                                </a>
                                            </div>
                                        @endif

                                    {{-- Bloc séparateur --}}
                                    @elseif($type === 'divider')
                                        <hr style="border:none; border-top:1px solid #e5e7eb; margin:16px 0;">

                                    @endif
                                @endforeach
                            </td>
                        </tr>

                        {{-- Footer / Désabonnement --}}
                        <tr>
                            <td style="padding:16px 24px 20px 24px; background-color:#f9fafb; border-top:1px solid #e5e7eb;">
                                <div style="font-family: Arial, sans-serif; font-size:11px; color:#6b7280; line-height:1.4;">
                                    Vous recevez cet email car vous êtes suivi(e) par
                                    <span style="font-weight:600; color:#111827;">
                                        {{ $newsletter->from_name }}
                                    </span>.
                                </div>

                                @if(!empty($unsubscribeUrl))
                                    <div style="margin-top:8px; font-family: Arial, sans-serif; font-size:11px; color:#9ca3af;">
                                        Si vous ne souhaitez plus recevoir ces emails,
                                        <a href="{{ $unsubscribeUrl }}" style="color:#647a0b; text-decoration:underline;">
                                            cliquez ici pour vous désabonner
                                        </a>.
                                    </div>
                                @else
                                    {{-- Prévisualisation / cas sans URL explicite --}}
                                    <div style="margin-top:8px; font-family: Arial, sans-serif; font-size:11px; color:#9ca3af;">
                                        Lien de désabonnement affiché ici dans l’email réel.
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
