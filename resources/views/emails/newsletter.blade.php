{{-- resources/views/emails/newsletter.blade.php --}}
@php
    $blocks = $newsletter->blocks ?? [];
    $clientFirstName = $client->first_name ?? '';
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $newsletter->subject }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Basic email-safe styles */
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        table {
            border-collapse: collapse;
        }
        .wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 24px 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .header {
            padding: 16px 24px;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .header-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }
        .body {
            padding: 24px;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }
        .footer {
            padding: 16px 24px;
            font-size: 11px;
            color: #6b7280;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            background-color: #647a0b;
            color: #ffffff !important;
        }
        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 16px 0;
        }
        h1, h2, h3 {
            color: #111827;
        }
        h1 { font-size: 22px; }
        h2 { font-size: 18px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" class="container">
            <tr>
                <td class="header">
                    <div class="header-title">
                        {{ $newsletter->from_name }}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="body">
                    @foreach($blocks as $block)
                        @php
                            $type = $block['type'] ?? 'text';

                            // simple variable replacement
                            $replaceVars = function($text) use ($clientFirstName) {
                                $text = str_replace('{{ client.first_name }}', $clientFirstName, $text);
                                return nl2br(e($text));
                            };
                        @endphp

                        @if($type === 'heading_text')
                            @if(!empty($block['heading']))
                                <h1 style="margin:0 0 8px 0;">
                                    {{ $block['heading'] }}
                                </h1>
                            @endif
                            @if(!empty($block['text']))
                                <p style="margin:0 0 16px 0;">
                                    {!! $replaceVars($block['text']) !!}
                                </p>
                            @endif
                        @elseif($type === 'text')
                            @if(!empty($block['text']))
                                <p style="margin:0 0 16px 0;">
                                    {!! $replaceVars($block['text']) !!}
                                </p>
                            @endif
                        @elseif($type === 'image' && !empty($block['url']))
                            <div style="margin:0 0 16px 0; text-align:center;">
                                <img src="{{ $block['url'] }}"
                                     alt="{{ $block['alt'] ?? '' }}"
                                     style="max-width:100%; border-radius:8px;">
                            </div>
                        @elseif($type === 'button' && !empty($block['url']))
                            <div style="margin:0 0 16px 0; text-align:center;">
                                <a href="{{ $block['url'] }}" class="btn">
                                    {{ $block['label'] ?? 'En savoir plus' }}
                                </a>
                            </div>
                        @elseif($type === 'divider')
                            <div class="divider"></div>
                        @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <div>
                        Vous recevez cet email car vous êtes suivi(e) par {{ $newsletter->from_name }}.
                    </div>
					@if(!empty($unsubscribeUrl))
						<p style="font-size:12px;color:#9ca3af;margin-top:8px;">
							Pour ne plus recevoir ces emails, 
							<a href="{{ $unsubscribeUrl }}" style="color:#647a0b;">
								cliquez ici pour vous désabonner
							</a>.
						</p>
					@endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
