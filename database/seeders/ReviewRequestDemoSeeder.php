<?php

namespace Database\Seeders;

use App\Mail\TestimonialRequestMail;
use App\Models\ClientProfile;
use App\Models\Testimonial;
use App\Models\TestimonialRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class ReviewRequestDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')
            || config('database.default') !== 'sqlite'
            || realpath(config('database.connections.sqlite.database')) !== realpath(storage_path('app/review-request-demo.sqlite'))
            || config('mail.default') !== 'log') {
            throw new RuntimeException('This demo requires the dedicated local review database and log mailer. Use scripts/start-review-preview.ps1.');
        }

        $therapist = User::firstOrCreate(['email' => 'therapeute.avis@olithea.test'], [
            'name' => 'Camille Démo',
            'company_name' => 'Cabinet Démo Olithea',
            'company_email' => 'cabinet.demo@olithea.test',
            'password' => Hash::make('DemoAvis-2026!'),
            'is_therapist' => true,
            'license_status' => 'active',
            'license_product' => 'new_pro_mensuelle',
            'slug' => 'camille-demo-avis',
        ]);
        $therapist->forceFill(['email_verified_at' => $therapist->email_verified_at ?? now()])->save();

        $scenarios = [
            ['Alice', 'Martin', 'alice.demo@example.test', null],
            ['Lucie', 'Bernard', 'lucie.demo@example.test', 'pending'],
            ['Nora', 'Petit', 'nora.demo@example.test', 'completed'],
            ['Camille', 'Robert', 'camille.demo@example.test', 'google'],
            ['Emma', 'Laurent', null, null],
        ];
        $googleUrl = 'https://g.page/r/DEMO-OLITHEA/review';
        $previews = [];

        foreach ($scenarios as [$firstName, $lastName, $email, $scenario]) {
            $client = ClientProfile::firstOrCreate([
                'user_id' => $therapist->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ], ['email' => $email, 'notes' => 'Client fictif pour la revue locale des demandes d’avis.']);

            if ($scenario) {
                $destination = $scenario === 'google' ? 'google' : null;
                $reviewRequest = TestimonialRequest::firstOrCreate([
                    'therapist_id' => $therapist->id,
                    'client_profile_id' => $client->id,
                    'destination' => $destination,
                ], [
                    'token' => Str::random(32),
                    'status' => $scenario === 'completed' ? 'completed' : 'pending',
                    'google_review_url' => $destination === 'google' ? $googleUrl : null,
                ]);

                if ($scenario === 'completed') {
                    Testimonial::firstOrCreate(['testimonial_request_id' => $reviewRequest->id], [
                        'therapist_id' => $therapist->id,
                        'client_profile_id' => $client->id,
                        'testimonial' => 'Un accompagnement attentif et bienveillant. Merci pour votre écoute !',
                    ]);
                }

                if ($scenario !== 'completed') {
                    $previews[$destination ?? 'internal'] = (new TestimonialRequestMail($reviewRequest))->render();
                }
            }

            $this->command?->line($firstName.' '.$lastName.': '.route('client_profiles.show', $client));
        }

        File::ensureDirectoryExists(storage_path('app/review-request-demo'));
        foreach ($previews as $destination => $html) {
            File::put(storage_path('app/review-request-demo/'.$destination.'-email.html'), $html);
        }

        $this->command?->info('Demo: therapeute.avis@olithea.test / DemoAvis-2026!');
        $this->command?->line('Emails are logged locally. The sample Google URL is fictitious.');
    }
}
