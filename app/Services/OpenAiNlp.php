<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAiNlp
{
    public function extract(string $message): array
    {
        $apiKey = config('services.openai.api_key');
        $model  = config('services.openai.model', 'gpt-4.1');

        if (empty($apiKey)) {
            return $this->fallbackRegex($message);
        }

        $system = <<<SYS
You are a strict intent/slot extractor for a therapist SaaS. Return ONLY compact JSON:
{
  "intent": "client.create" | "unknown",
  "slots": {
    "first_name": string|null,
    "last_name": string|null,
    "email": string|null,
    "phone": string|null,
    "notes": string|null
  },
  "missing_slots": string[],
  "confidence": number
}
Rules:
- intent limited to "client.create" or "unknown".
- If only one name is given, treat it as first_name; set last_name missing.
- Email must be valid looking or null.
- Phone: keep + and digits only, else null.
- No extra text. Only JSON.
SYS;

$resp = Http::withHeaders([
        'Authorization' => "Bearer {$apiKey}",
        'Content-Type'  => 'application/json',
    ])
    ->withOptions([
        // keep your cert path fix; adjust if you used an env var
        'verify' => storage_path('certs/cacert.pem'),
    ])
    ->post('https://api.openai.com/v1/chat/completions', [
        'model' => $model,
        'temperature' => 0,
        'response_format' => ['type' => 'json_object'],
        'messages' => [
            ['role' => 'system', 'content' => $system],
            // ❌ was: ['role' => 'user', 'content' => $user]
            // ✅ inline the content:
            ['role' => 'user',   'content' => "Message: {$message}"],
        ],
    ])->throw()->json();



        $content = data_get($resp, 'choices.0.message.content', '{}');
        $out = json_decode($content, true);

        if (!is_array($out)) {
            return ['intent' => 'unknown','slots'=>[],'missing_slots'=>[],'confidence'=>0.0];
        }
        return [
            'intent' => $out['intent'] ?? 'unknown',
            'slots' => $out['slots'] ?? [],
            'missing_slots' => $out['missing_slots'] ?? [],
            'confidence' => $out['confidence'] ?? 0.0,
        ];
    }

    private function fallbackRegex(string $m): array
    {
        $intent = preg_match('/\b(client|cr[eé]er|add|new)\b/i', $m) ? 'client.create' : 'unknown';
        preg_match('/\b([^\s@]+@[^\s@]+\.[^\s@]+)\b/', $m, $em);
        preg_match('/(\+?\d[\d\s]{6,})/', $m, $ph);

        // crude name extraction: first two words after trigger words
        $name = null;
        if (preg_match('/(?:cr[eé]er|create|add|new)\s+client\s+([^\d,]+?)(?:,|$)/i', $m, $nm)) {
            $name = trim($nm[1]);
        } elseif (preg_match('/client\s+([^\d,]+?)(?:,|$)/i', $m, $nm)) {
            $name = trim($nm[1]);
        }
        $first = $last = null;
        if ($name) {
            $parts = preg_split('/\s+/', trim($name));
            $first = $parts[0] ?? null;
            $last = $parts[1] ?? null;
        }

        $slots = [
            'first_name' => $first,
            'last_name'  => $last,
            'email'      => $em[1] ?? null,
            'phone'      => isset($ph[1]) ? preg_replace('/\s+/', '', $ph[1]) : null,
            'notes'      => null,
        ];

        $missing = [];
        if (!$slots['first_name']) $missing[] = 'first_name';
        if (!$slots['last_name'])  $missing[] = 'last_name';

        return [
            'intent' => $intent,
            'slots'  => $slots,
            'missing_slots' => $missing,
            'confidence' => $intent === 'client.create' ? 0.6 : 0.0,
        ];
    }
}
