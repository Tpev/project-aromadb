<?php

namespace Database\Seeders;

use App\Models\ClientProfile;
use App\Models\Conseil;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConseilLinksDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Enforces the dedicated local database and log-only mail before any write.
        $this->call(ReviewRequestDemoSeeder::class);
        $therapist = User::where('email', 'therapeute.avis@olithea.test')->sole();
        $client = ClientProfile::where('user_id', $therapist->id)->where('email', 'alice.demo@example.test')->sole();

        $conseil = Conseil::firstOrCreate([
            'user_id' => $therapist->id, 'name' => 'Conseil démo · texte et liens',
        ], [
            'tag' => 'Démonstration',
            'content' => '<p>Voici un exemple de conseil accompagné de liens utiles.</p><p>Les adresses ci-dessous ouvrent des pages de démonstration : vous pourrez les remplacer par vos vidéos, articles ou autres ressources.</p>',
            'links' => [
                ['label' => 'Ouvrir la ressource de démonstration', 'url' => 'https://example.org/'],
                ['label' => null, 'url' => 'https://example.com/'],
            ],
        ]);

        $sent = $client->conseilsSent()->where('conseil_id', $conseil->id)->first();
        $token = $sent?->pivot->token ?? Str::random(64);
        if (! $sent) {
            $client->conseilsSent()->attach($conseil->id, ['token' => $token, 'sent_at' => now()]);
        }

        $this->command?->line('Modifier le conseil : '.route('conseils.edit', $conseil));
        $this->command?->line('Vue client : '.route('public.conseil.view', [
            'clientProfile' => $client->id, 'conseil' => $conseil->id, 'token' => $token,
        ]));
        $this->command?->line('Exemple local uniquement. Aucun email envoyé.');
    }
}
