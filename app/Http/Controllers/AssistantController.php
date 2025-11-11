<?php

namespace App\Http\Controllers;

use App\Models\AssistantSession;
use App\Services\OpenAiNlp;
use App\Services\CreateClientAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AssistantController extends Controller
{
    public function view()
    {
        return view('assistant.chat');
    }

    public function message(Request $request, OpenAiNlp $nlp, CreateClientAction $creator)
    {
        $request->validate(['text' => 'required|string|max:2000']);
        $userId = Auth::id();

        $session = AssistantSession::firstOrCreate(
            ['user_id' => $userId],
            ['expires_at' => now()->addMinutes(30)]
        );

        // reset if expired
        if ($session->expires_at && $session->expires_at->isPast()) {
            $session->update([
                'current_intent' => null,
                'collected_slots' => null,
                'missing_slots' => null,
                'awaiting_confirmation' => false,
                'expires_at' => now()->addMinutes(30),
            ]);
        } else {
            $session->update(['expires_at' => now()->addMinutes(30)]);
        }

        $text = trim($request->string('text'));

        // awaiting confirmation path
        if ($session->awaiting_confirmation) {
            if (preg_match('/^(oui|yes|ok)$/i', $text)) {
                try {
                    $client = $creator->run($userId, $session->collected_slots ?? []);
                } catch (ValidationException $e) {
                    // unlikely here, but handle gracefully
                    $session->update(['awaiting_confirmation' => false]);
                    return response()->json(['messages' => [[
                        'role' => 'assistant',
                        'text' => 'Les données ne sont pas valides: '.$e->validator->errors()->first(),
                    ]]], 200);
                }

                $session->update([
                    'current_intent' => null,
                    'collected_slots' => null,
                    'missing_slots' => null,
                    'awaiting_confirmation' => false,
                ]);

                return response()->json([
                    'messages' => [[
                        'role' => 'assistant',
                        'text' => "✅ Client créé: {$client->first_name} {$client->last_name}.",
                        'link' => route('client_profiles.show', $client->id),
                        'link_label' => 'Ouvrir la fiche'
                    ]]
                ]);
            }

            if (preg_match('/^(non|no|annuler|cancel)$/i', $text)) {
                $session->update([
                    'current_intent' => null,
                    'collected_slots' => null,
                    'missing_slots' => null,
                    'awaiting_confirmation' => false,
                ]);
                return response()->json([
                    'messages' => [[
                        'role' => 'assistant',
                        'text' => "D’accord, j’annule. Dites “Créer client …” pour recommencer."
                    ]]
                ]);
            }

            return response()->json([
                'messages' => [[ 'role' => 'assistant', 'text' => "Confirmez-vous la création ? (Oui / Non)" ]]
            ]);
        }

        // NLP extraction
        $parsed = $nlp->extract($text);

        if (($session->current_intent ?? null) === 'client.create' || $parsed['intent'] === 'client.create') {
            $intent = 'client.create';
            $slots  = $session->collected_slots ?? [];
            foreach (($parsed['slots'] ?? []) as $k => $v) {
                if ($v !== null) $slots[$k] = $v;
            }

            $missing = [];
            if (empty($slots['first_name'])) $missing[] = 'first_name';
            if (empty($slots['last_name']))  $missing[] = 'last_name';

            if ($missing) {
                $session->update([
                    'current_intent' => $intent,
                    'collected_slots' => $slots,
                    'missing_slots' => $missing,
                    'awaiting_confirmation' => false,
                ]);

                $next = in_array('last_name', $missing) ? 'last_name' : $missing[0];
                $q = match($next) {
                    'first_name' => "Quel est le prénom ?",
                    'last_name'  => "Quel nom de famille pour ".($slots['first_name'] ?? 'le client')." ?",
                    default      => "Pouvez-vous préciser : {$next} ?",
                };

                return response()->json(['messages' => [[ 'role' => 'assistant', 'text' => $q ]]], 200);
            }

            // confirm
            $session->update([
                'current_intent' => $intent,
                'collected_slots' => $slots,
                'missing_slots' => [],
                'awaiting_confirmation' => true,
            ]);

            $email = $slots['email'] ?? '–';
            $phone = $slots['phone'] ?? '–';
            $preview = "Créer le client **{$slots['first_name']} {$slots['last_name']}** (email: {$email}, tel: {$phone}) ? (Oui / Non)";

            return response()->json(['messages' => [[ 'role' => 'assistant', 'text' => $preview ]]], 200);
        }

        if (($parsed['confidence'] ?? 0) < 0.6 || $parsed['intent'] === 'unknown') {
            return response()->json(['messages' => [[
                'role' => 'assistant',
                'text' => "Je n’ai pas bien saisi. Essayez : *Créer client Claire Dupont, email claire@ex.com, téléphone 0611223344*."
            ]]], 200);
        }

        return response()->json(['messages' => [[ 'role' => 'assistant', 'text' => "D’accord. Que souhaitez-vous faire ?" ]]], 200);
    }
}
