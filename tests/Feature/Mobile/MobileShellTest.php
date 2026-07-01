<?php

use App\Jobs\SendGiftVoucherEmailsJob;
use App\Mail\DocumentSignRequestMail;
use App\Models\Audience;
use App\Models\ClientFile;
use App\Models\Document;
use App\Models\DocumentSigning;
use App\Models\Emargement;
use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Models\User;
use App\Models\Product;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\ClientProfile;
use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use App\Models\CommunityMessage;
use App\Models\CorporateClient;
use App\Models\Event;
use App\Models\GiftVoucher;
use App\Models\GoogleBusinessAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryItem;
use App\Models\Newsletter;
use App\Models\NewsletterMonthlyUsage;
use App\Models\NewsletterRecipient;
use App\Models\Message;
use App\Models\Metric;
use App\Models\MetricEntry;
use App\Models\PackProduct;
use App\Models\PackProductItem;
use App\Models\PackPurchase;
use App\Models\PackPurchaseItem;
use App\Models\PracticeLocation;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\ReferralCode;
use App\Models\ReferralInvite;
use App\Models\Receipt;
use App\Models\Reservation;
use App\Models\SessionNote;
use App\Models\SessionNoteTemplate;
use App\Models\SuperPdpConnection;
use App\Models\SuperPdpReceivedInvoice;
use App\Models\Testimonial;
use App\Models\TrainingBlock;
use App\Models\TrainingModule;
use App\Services\SuperPdp\SuperPdpApiClient;
use App\Services\SuperPdp\SuperPdpReceivedInvoiceSyncService;
use App\Services\FrenchAddressGeocodingService;
use App\Mail\NewReservationNotification;
use App\Mail\ReservationConfirmation;
use App\Mail\TherapistInviteMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

test('mobile entry and practitioner login render', function () {
    $this->get('/mobile')
        ->assertOk()
        ->assertSee('Application mobile AromaMade PRO');

    $this->get('/mobile/login')
        ->assertOk()
        ->assertSee('Espace praticien')
        ->assertSee('Connexion');
});

test('mobile protected routes send guests to mobile login', function () {
    $this->get('/mobile/menu')
        ->assertRedirect('/mobile/login');

    $this->get('/mobile/pro/more')
        ->assertRedirect('/mobile/menu');
});

test('mobile login sends practitioners to the mobile dashboard', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->post('/mobile/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('mobile.dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('authenticated practitioners can open the mobile menu', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/menu')
        ->assertOk()
        ->assertSee('Tous les modules AromaMade PRO')
        ->assertSee('Factures &amp; devis', false)
        ->assertSee('Bons cadeaux');
});

test('authenticated practitioners can open mobile module overviews', function (string $path, string $label) {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get($path)
        ->assertOk()
        ->assertSee($label);
})->with([
    ['/mobile/prestations', 'Prestations'],
    ['/mobile/disponibilites', 'Disponibilites'],
    ['/mobile/lieux', 'Lieux de pratique'],
    ['/mobile/questionnaires', 'Questionnaires'],
    ['/mobile/evenements', 'Evenements'],
    ['/mobile/documents', 'Documents clients'],
    ['/mobile/emargements', 'Emargements'],
    ['/mobile/recettes', 'Livre de recettes'],
    ['/mobile/stock', 'Stock'],
    ['/mobile/entreprises', 'Entreprises'],
    ['/mobile/packs', 'Packs'],
    ['/mobile/bons-cadeaux', 'Bons cadeaux'],
    ['/mobile/factures-recues', 'Factures recues'],
    ['/mobile/formations-digitales', 'Formations digitales'],
    ['/mobile/communautes', 'Communautes'],
    ['/mobile/audiences', 'Audiences'],
    ['/mobile/newsletters', 'Newsletters'],
    ['/mobile/avis-google', 'Avis Google'],
    ['/mobile/parrainage', 'Parrainage'],
    ['/mobile/profil', 'Profil'],
    ['/mobile/abonnement', 'Abonnement'],
]);

test('authenticated practitioners can manage client documents from mobile', function () {
    Mail::fake();
    Storage::fake('public');

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Claire',
        'last_name' => 'Martin',
        'email' => 'claire@example.test',
        'password' => 'client-password',
    ]);

    $this->actingAs($user)
        ->get('/mobile/documents')
        ->assertOk()
        ->assertSee('Documents clients')
        ->assertSee('Claire Martin');

    $this->actingAs($user)
        ->get(route('mobile.documents.client', $client, false))
        ->assertOk()
        ->assertSee('Fichier partage')
        ->assertSee('PDF a signer');

    $this->actingAs($user)
        ->post(route('mobile.documents.files.store', $client, false), [
            'file' => UploadedFile::fake()->create('bilan-client.txt', 8, 'text/plain'),
        ])
        ->assertRedirect(route('mobile.documents.client', $client, false));

    $file = ClientFile::firstOrFail();
    Storage::disk('public')->assertExists($file->file_path);

    $this->actingAs($user)
        ->get(route('mobile.documents.files.download', [$client, $file], false))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('mobile.documents.signatures.store', $client, false), [
            'file' => UploadedFile::fake()->create('mandat.pdf', 12, 'application/pdf'),
        ])
        ->assertRedirect(route('mobile.documents.client', $client, false));

    $document = Document::firstOrFail();
    Storage::disk('public')->assertExists($document->storage_path);

    $this->actingAs($user)
        ->post(route('mobile.documents.signatures.send', $document, false))
        ->assertRedirect(route('mobile.documents.client', $client, false));

    $document->refresh();
    expect($document->status)->toBe('sent');

    $signing = DocumentSigning::where('document_id', $document->id)->firstOrFail();
    Mail::assertQueued(DocumentSignRequestMail::class, function (DocumentSignRequestMail $mail) use ($document) {
        return $mail->document->is($document);
    });

    $oldToken = $signing->token;

    $this->actingAs($user)
        ->post(route('mobile.documents.signatures.resend', $signing, false))
        ->assertRedirect(route('mobile.documents.client', $client, false));

    expect($signing->fresh()->token)->not->toBe($oldToken);
    Mail::assertQueued(DocumentSignRequestMail::class, 2);

    $this->actingAs($user)
        ->delete(route('mobile.documents.files.destroy', [$client, $file], false))
        ->assertRedirect(route('mobile.documents.client', $client, false));

    $this->assertDatabaseMissing('client_files', ['id' => $file->id]);
});

test('mobile document actions are protected by ownership', function () {
    Storage::fake('public');

    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Nina',
        'last_name' => 'Bernard',
        'email' => 'nina@example.test',
    ]);

    Storage::disk('public')->put('client_files/owned/file.txt', 'private file');
    Storage::disk('public')->put('documents/originals/owned.pdf', 'private pdf');

    $file = ClientFile::create([
        'client_profile_id' => $client->id,
        'file_path' => 'client_files/owned/file.txt',
        'original_name' => 'file.txt',
        'mime_type' => 'text/plain',
        'size' => 12,
    ]);

    $document = Document::create([
        'owner_user_id' => $owner->id,
        'client_profile_id' => $client->id,
        'original_name' => 'owned.pdf',
        'storage_path' => 'documents/originals/owned.pdf',
        'status' => 'draft',
        'uploaded_by_user_id' => $owner->id,
    ]);

    $this->actingAs($other)
        ->get(route('mobile.documents.client', $client, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->get(route('mobile.documents.files.download', [$client, $file], false))
        ->assertForbidden();

    $this->actingAs($other)
        ->post(route('mobile.documents.signatures.send', $document, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->get(route('mobile.documents.signatures.original', $document, false))
        ->assertForbidden();
});

test('authenticated practitioners can manage client session notes from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Claire',
        'last_name' => 'Durand',
        'email' => 'claire.notes@example.test',
    ]);

    $template = SessionNoteTemplate::create([
        'user_id' => $user->id,
        'title' => 'Bilan rapide',
        'content' => '<p>Respiration, fatigue, prochaines actions.</p>',
    ]);

    $this->actingAs($user)
        ->get(route('mobile.clients.show', $client, false))
        ->assertOk()
        ->assertSee('Notes de seance')
        ->assertSee('0 note(s)');

    $this->actingAs($user)
        ->get(route('mobile.session-notes.index', $client, false))
        ->assertOk()
        ->assertSee('Notes de seance')
        ->assertSee('Claire Durand');

    $this->actingAs($user)
        ->get(route('mobile.session-notes.create', $client, false))
        ->assertOk()
        ->assertSee('Nouvelle note')
        ->assertSee('Bilan rapide');

    $this->actingAs($user)
        ->post(route('mobile.session-notes.store', $client, false), [
            'session_note_template_id' => $template->id,
            'note' => 'Respiration plus calme. Revoir les exercices.',
        ])
        ->assertRedirect(route('mobile.session-notes.index', $client, false));

    $note = SessionNote::firstOrFail();

    expect($note->client_profile_id)->toBe($client->id)
        ->and($note->user_id)->toBe($user->id)
        ->and($note->session_note_template_id)->toBe($template->id);

    $this->actingAs($user)
        ->get(route('mobile.session-notes.show', $note, false))
        ->assertOk()
        ->assertSee('Respiration plus calme')
        ->assertSee('Bilan rapide');

    $this->actingAs($user)
        ->get(route('mobile.session-notes.edit', $note, false))
        ->assertOk()
        ->assertSee('Modifier la note');

    $this->actingAs($user)
        ->put(route('mobile.session-notes.update', $note, false), [
            'note' => 'Suite: respiration stable.',
        ])
        ->assertRedirect(route('mobile.session-notes.show', $note, false));

    $this->assertDatabaseHas('session_notes', [
        'id' => $note->id,
        'note' => 'Suite: respiration stable.',
    ]);

    $this->actingAs($user)
        ->delete(route('mobile.session-notes.destroy', $note, false))
        ->assertRedirect(route('mobile.session-notes.index', $client, false));

    $this->assertDatabaseMissing('session_notes', ['id' => $note->id]);
});

test('mobile session notes are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Nina',
        'last_name' => 'Privee',
        'email' => 'nina.notes@example.test',
    ]);

    $note = SessionNote::create([
        'client_profile_id' => $client->id,
        'user_id' => $owner->id,
        'note' => 'Note confidentielle',
    ]);

    $this->actingAs($other)
        ->get(route('mobile.session-notes.index', $client, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->post(route('mobile.session-notes.store', $client, false), [
            'note' => 'Intrusion',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->get(route('mobile.session-notes.show', $note, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->put(route('mobile.session-notes.update', $note, false), [
            'note' => 'Modification interdite',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('mobile.session-notes.destroy', $note, false))
        ->assertForbidden();
});

test('authenticated practitioners can manage client metrics from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Suivi',
        'email' => 'camille.metrics@example.test',
    ]);

    $this->actingAs($user)
        ->get(route('mobile.clients.show', $client, false))
        ->assertOk()
        ->assertSee('Suivi des mesures')
        ->assertSee('0 mesure(s)');

    $this->actingAs($user)
        ->get(route('mobile.metrics.index', $client, false))
        ->assertOk()
        ->assertSee('Suivi des mesures')
        ->assertSee('Camille Suivi');

    $this->actingAs($user)
        ->get(route('mobile.metrics.create', $client, false))
        ->assertOk()
        ->assertSee('Nouvelle mesure');

    $this->actingAs($user)
        ->post(route('mobile.metrics.store', $client, false), [
            'name' => 'Douleur',
            'goal' => 2,
        ])
        ->assertRedirect();

    $metric = Metric::firstOrFail();

    expect($metric->client_profile_id)->toBe($client->id)
        ->and($metric->name)->toBe('Douleur');

    $this->actingAs($user)
        ->get(route('mobile.metrics.show', [$client, $metric], false))
        ->assertOk()
        ->assertSee('Douleur')
        ->assertSee('Ajouter une valeur');

    $this->actingAs($user)
        ->post(route('mobile.metrics.entries.store', [$client, $metric], false), [
            'entry_date' => '2026-07-01',
            'value' => 4.5,
        ])
        ->assertRedirect(route('mobile.metrics.show', [$client, $metric], false));

    $entry = MetricEntry::firstOrFail();

    expect($entry->metric_id)->toBe($metric->id)
        ->and((float) $entry->value)->toBe(4.5);

    $this->actingAs($user)
        ->get(route('mobile.metrics.entries.edit', [$client, $metric, $entry], false))
        ->assertOk()
        ->assertSee('Modifier la valeur');

    $this->actingAs($user)
        ->put(route('mobile.metrics.entries.update', [$client, $metric, $entry], false), [
            'entry_date' => '2026-07-02',
            'value' => 3,
        ])
        ->assertRedirect(route('mobile.metrics.show', [$client, $metric], false));

    $this->assertDatabaseHas('metric_entries', [
        'id' => $entry->id,
        'entry_date' => '2026-07-02',
    ]);

    $this->actingAs($user)
        ->put(route('mobile.metrics.update', [$client, $metric], false), [
            'name' => 'Douleur matin',
            'goal' => 1,
        ])
        ->assertRedirect(route('mobile.metrics.show', [$client, $metric], false));

    $this->assertDatabaseHas('metrics', [
        'id' => $metric->id,
        'name' => 'Douleur matin',
    ]);

    $this->actingAs($user)
        ->delete(route('mobile.metrics.entries.destroy', [$client, $metric, $entry], false))
        ->assertRedirect(route('mobile.metrics.show', [$client, $metric], false));

    $this->assertDatabaseMissing('metric_entries', ['id' => $entry->id]);

    $this->actingAs($user)
        ->delete(route('mobile.metrics.destroy', [$client, $metric], false))
        ->assertRedirect(route('mobile.metrics.index', $client, false));

    $this->assertDatabaseMissing('metrics', ['id' => $metric->id]);
});

test('mobile metrics are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Mina',
        'last_name' => 'Privee',
        'email' => 'mina.metrics@example.test',
    ]);

    $metric = $client->metrics()->create([
        'name' => 'Sommeil',
        'goal' => 8,
    ]);

    $entry = $metric->entries()->create([
        'entry_date' => '2026-07-01',
        'value' => 6,
    ]);

    $this->actingAs($other)
        ->get(route('mobile.metrics.index', $client, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->post(route('mobile.metrics.store', $client, false), [
            'name' => 'Intrusion',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->get(route('mobile.metrics.show', [$client, $metric], false))
        ->assertForbidden();

    $this->actingAs($other)
        ->put(route('mobile.metrics.update', [$client, $metric], false), [
            'name' => 'Modification interdite',
            'goal' => 1,
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post(route('mobile.metrics.entries.store', [$client, $metric], false), [
            'entry_date' => '2026-07-02',
            'value' => 1,
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('mobile.metrics.entries.destroy', [$client, $metric, $entry], false))
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('mobile.metrics.destroy', [$client, $metric], false))
        ->assertForbidden();
});

test('authenticated practitioners can manage emargements from mobile', function () {
    Queue::fake();
    Storage::fake('public');

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Renee',
        'last_name' => 'Leroy',
        'email' => 'renee@example.test',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Atelier signe',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => true,
        'requires_emargement' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $appointment = Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->addDay()->setTime(14, 0),
        'duration' => 60,
        'status' => 'Programme',
    ]);

    $this->actingAs($user)
        ->get('/mobile/emargements')
        ->assertOk()
        ->assertSee('Emargements')
        ->assertSee('Renee Leroy')
        ->assertSee('Atelier signe')
        ->assertSee('Envoyer');

    $this->actingAs($user)
        ->post(route('mobile.emargements.send', $appointment, false))
        ->assertRedirect(route('mobile.emargements.index', absolute: false));

    $emargement = Emargement::firstOrFail();

    expect($emargement->status)->toBe('pending')
        ->and($emargement->client_email)->toBe('renee@example.test')
        ->and($appointment->fresh()->emargement_sent)->toBeTrue();

    $oldToken = $emargement->token;

    $this->actingAs($user)
        ->post(route('mobile.emargements.resend', $emargement, false))
        ->assertRedirect(route('mobile.emargements.index', absolute: false));

    expect($emargement->fresh()->token)->not->toBe($oldToken);

    Storage::disk('public')->put('emargements/test-proof.pdf', 'signed proof');
    $emargement->update([
        'status' => 'signed',
        'signed_at' => now(),
        'pdf_path' => 'emargements/test-proof.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('mobile.emargements.download', $emargement, false))
        ->assertOk();
});

test('mobile emargement actions are protected by ownership', function () {
    Storage::fake('public');

    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Sofia',
        'last_name' => 'Moreau',
        'email' => 'sofia@example.test',
    ]);

    $product = Product::create([
        'user_id' => $owner->id,
        'name' => 'Seance privee',
        'price' => 90,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => true,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
        'requires_emargement' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $appointment = Appointment::create([
        'user_id' => $owner->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->addDays(2)->setTime(10, 0),
        'duration' => 60,
        'status' => 'Programme',
    ]);

    Storage::disk('public')->put('emargements/private-proof.pdf', 'private proof');

    $emargement = Emargement::create([
        'appointment_id' => $appointment->id,
        'therapist_id' => $owner->id,
        'client_email' => $client->email,
        'token' => str_repeat('a', 64),
        'expires_at' => now()->addDays(14),
        'status' => 'pending',
        'pdf_path' => 'emargements/private-proof.pdf',
    ]);

    $this->actingAs($other)
        ->post(route('mobile.emargements.send', $appointment, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->post(route('mobile.emargements.resend', $emargement, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->get(route('mobile.emargements.download', $emargement, false))
        ->assertForbidden();
});

test('authenticated practitioners can create a questionnaire from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/questionnaires/create')
        ->assertOk()
        ->assertSee('Nouveau questionnaire')
        ->assertSee('Questions');

    $response = $this->actingAs($user)
        ->post('/mobile/questionnaires', [
            'title' => 'Bilan mobile',
            'description' => 'A remplir avant la seance.',
            'questions' => [
                [
                    'text' => 'Quel est votre objectif principal ?',
                    'type' => 'text',
                ],
                [
                    'text' => 'Quel rythme preferez-vous ?',
                    'type' => 'multiple_choice',
                    'options' => 'Matin, Apres-midi, Soir',
                ],
            ],
        ]);

    $questionnaire = Questionnaire::where('user_id', $user->id)
        ->where('title', 'Bilan mobile')
        ->firstOrFail();

    $response
        ->assertRedirect(route('mobile.questionnaires.show', $questionnaire, absolute: false))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('questionnaires', [
        'id' => $questionnaire->id,
        'user_id' => $user->id,
        'description' => 'A remplir avant la seance.',
    ]);

    $this->assertDatabaseHas('questions', [
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Quel est votre objectif principal ?',
        'type' => 'text',
    ]);
    $this->assertDatabaseHas('questions', [
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Quel rythme preferez-vous ?',
        'type' => 'multiple_choice',
        'options' => 'Matin, Apres-midi, Soir',
    ]);
});

test('authenticated practitioners can view update and delete their questionnaire from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $questionnaire = Questionnaire::create([
        'user_id' => $user->id,
        'title' => 'Suivi initial',
        'description' => 'Ancienne description',
    ]);
    $firstQuestion = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Question initiale',
        'type' => 'text',
    ]);
    $removedQuestion = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Question a retirer',
        'type' => 'multiple_choice',
    ]);
    $removedQuestion->forceFill([
        'options' => 'Oui, Non',
    ])->save();

    $this->actingAs($user)
        ->get("/mobile/questionnaires/{$questionnaire->id}")
        ->assertOk()
        ->assertSee('Suivi initial')
        ->assertSee('Question initiale')
        ->assertSee('Question a retirer');

    $this->actingAs($user)
        ->get("/mobile/questionnaires/{$questionnaire->id}/edit")
        ->assertOk()
        ->assertSee('Modifier le questionnaire')
        ->assertSee('Question initiale');

    $this->actingAs($user)
        ->put("/mobile/questionnaires/{$questionnaire->id}", [
            'title' => 'Suivi mobile actualise',
            'description' => 'Nouvelle description',
            'questions' => [
                [
                    'id' => $firstQuestion->id,
                    'text' => 'Question transformee',
                    'type' => 'multiple_choice',
                    'options' => 'Une fois, Plusieurs fois',
                ],
                [
                    'text' => 'Nouvelle question mobile',
                    'type' => 'text',
                ],
            ],
        ])
        ->assertRedirect(route('mobile.questionnaires.show', $questionnaire, absolute: false))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('questionnaires', [
        'id' => $questionnaire->id,
        'title' => 'Suivi mobile actualise',
        'description' => 'Nouvelle description',
    ]);
    $this->assertDatabaseHas('questions', [
        'id' => $firstQuestion->id,
        'text' => 'Question transformee',
        'type' => 'multiple_choice',
        'options' => 'Une fois, Plusieurs fois',
    ]);
    $this->assertDatabaseHas('questions', [
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Nouvelle question mobile',
        'type' => 'text',
    ]);
    $this->assertDatabaseMissing('questions', [
        'id' => $removedQuestion->id,
    ]);

    $this->actingAs($user)
        ->delete("/mobile/questionnaires/{$questionnaire->id}")
        ->assertRedirect(route('mobile.questionnaires.index', absolute: false))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('questionnaires', [
        'id' => $questionnaire->id,
    ]);
});

test('mobile questionnaire actions are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $questionnaire = Questionnaire::create([
        'user_id' => $owner->id,
        'title' => 'Questionnaire prive',
        'description' => 'Compte proprietaire',
    ]);
    $question = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Question privee',
        'type' => 'text',
    ]);

    $ownQuestionnaire = Questionnaire::create([
        'user_id' => $other->id,
        'title' => 'Questionnaire autorise',
        'description' => null,
    ]);

    $this->actingAs($other)
        ->get("/mobile/questionnaires/{$questionnaire->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->get("/mobile/questionnaires/{$questionnaire->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/questionnaires/{$questionnaire->id}", [
            'title' => 'Tentative',
            'questions' => [
                [
                    'text' => 'Tentative',
                    'type' => 'text',
                ],
            ],
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/questionnaires/{$questionnaire->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/questionnaires/{$questionnaire->id}/questions/{$question->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/questionnaires/{$ownQuestionnaire->id}", [
            'title' => 'Questionnaire autorise',
            'questions' => [
                [
                    'id' => $question->id,
                    'text' => 'Question piratee',
                    'type' => 'text',
                ],
            ],
        ])
        ->assertForbidden();
});

test('authenticated practitioners can create an event from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Atelier mobile',
        'description' => 'Support evenement',
        'price' => 40,
        'tax_rate' => 0,
        'duration' => 60,
    ]);

    $start = now()->addDays(8)->setTime(14, 30);

    $this->actingAs($user)
        ->get('/mobile/evenements/create')
        ->assertOk()
        ->assertSee('Nouvel evenement')
        ->assertSee('Reservations');

    $response = $this->actingAs($user)
        ->post('/mobile/evenements', [
            'name' => 'Atelier respiration mobile',
            'description' => 'Pratique en petit groupe',
            'start_date_time' => $start->format('Y-m-d\TH:i'),
            'duration' => 90,
            'booking_required' => 1,
            'limited_spot' => 1,
            'number_of_spot' => 12,
            'associated_product' => $product->id,
            'showOnPortail' => 1,
            'location' => 'Studio mobile',
            'event_type' => 'in_person',
            'collect_payment' => 0,
            'tax_rate' => 0,
        ]);

    $event = Event::where('user_id', $user->id)
        ->where('name', 'Atelier respiration mobile')
        ->firstOrFail();

    $response
        ->assertRedirect(route('mobile.events.show', $event, absolute: false))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'user_id' => $user->id,
        'duration' => 90,
        'booking_required' => true,
        'limited_spot' => true,
        'number_of_spot' => 12,
        'associated_product' => $product->id,
        'showOnPortail' => true,
        'location' => 'Studio mobile',
        'event_type' => 'in_person',
        'collect_payment' => false,
    ]);
});

test('authenticated practitioners can view update and delete their event from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);

    $event = Event::create([
        'user_id' => $user->id,
        'name' => 'Atelier initial',
        'description' => 'Ancienne description',
        'start_date_time' => now()->addDays(5),
        'duration' => 60,
        'booking_required' => true,
        'limited_spot' => true,
        'number_of_spot' => 6,
        'associated_product' => null,
        'image' => null,
        'showOnPortail' => true,
        'location' => 'Cabinet',
        'event_type' => 'in_person',
        'collect_payment' => false,
        'price' => null,
        'tax_rate' => 0,
    ]);
    Reservation::create([
        'event_id' => $event->id,
        'full_name' => 'Client Mobile',
        'email' => 'client-mobile@example.test',
        'phone' => '0600000000',
        'status' => 'confirmed',
    ]);

    $this->actingAs($user)
        ->get("/mobile/evenements/{$event->id}")
        ->assertOk()
        ->assertSee('Atelier initial')
        ->assertSee('Client Mobile')
        ->assertSee('Modifier');

    $this->actingAs($user)
        ->get("/mobile/evenements/{$event->id}/edit")
        ->assertOk()
        ->assertSee('Modifier l evenement')
        ->assertSee('Atelier initial');

    $this->actingAs($user)
        ->put("/mobile/evenements/{$event->id}", [
            'name' => 'Visio mobile actualisee',
            'description' => 'Nouvelle description',
            'start_date_time' => now()->addDays(10)->setTime(9, 0)->format('Y-m-d\TH:i'),
            'duration' => 45,
            'booking_required' => 0,
            'limited_spot' => 0,
            'number_of_spot' => null,
            'associated_product' => null,
            'showOnPortail' => 0,
            'location' => null,
            'event_type' => 'visio',
            'visio_provider' => 'external',
            'visio_url' => 'https://example.test/visio-mobile',
            'collect_payment' => 0,
            'tax_rate' => 0,
        ])
        ->assertRedirect(route('mobile.events.show', $event, absolute: false))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'name' => 'Visio mobile actualisee',
        'description' => 'Nouvelle description',
        'duration' => 45,
        'booking_required' => false,
        'limited_spot' => false,
        'number_of_spot' => null,
        'showOnPortail' => false,
        'location' => 'En ligne (Visio)',
        'event_type' => 'visio',
        'visio_provider' => 'external',
        'visio_url' => 'https://example.test/visio-mobile',
    ]);

    $this->actingAs($user)
        ->delete("/mobile/evenements/{$event->id}")
        ->assertRedirect(route('mobile.events.index', absolute: false))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('events', [
        'id' => $event->id,
    ]);
});

test('authenticated practitioners can add client participants to their event from mobile', function () {
    Mail::fake();

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);

    $event = Event::create([
        'user_id' => $user->id,
        'name' => 'Atelier participants',
        'description' => null,
        'start_date_time' => now()->addDays(3),
        'duration' => 60,
        'booking_required' => true,
        'limited_spot' => true,
        'number_of_spot' => 2,
        'associated_product' => null,
        'image' => null,
        'showOnPortail' => true,
        'location' => 'Salle mobile',
        'event_type' => 'in_person',
        'collect_payment' => false,
        'price' => null,
        'tax_rate' => 0,
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Lea',
        'last_name' => 'Mobile',
        'email' => 'lea-mobile@example.test',
        'phone' => '0611111111',
    ]);
    $otherClient = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Noe',
        'last_name' => 'Mobile',
        'email' => 'noe-mobile@example.test',
    ]);

    $this->actingAs($user)
        ->post("/mobile/evenements/{$event->id}/participants", [
            'client_profile_id' => $client->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $reservation = Reservation::where('event_id', $event->id)
        ->where('email', 'lea-mobile@example.test')
        ->firstOrFail();

    expect($reservation->full_name)->toBe('Lea Mobile');

    Mail::assertQueued(ReservationConfirmation::class);
    Mail::assertQueued(NewReservationNotification::class);

    $this->actingAs($user)
        ->post("/mobile/evenements/{$event->id}/participants", [
            'client_profile_id' => $client->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->actingAs($user)
        ->post("/mobile/evenements/{$event->id}/participants", [
            'client_profile_id' => $otherClient->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $fullEvent = Event::create([
        'user_id' => $user->id,
        'name' => 'Atelier complet',
        'description' => null,
        'start_date_time' => now()->addDays(4),
        'duration' => 60,
        'booking_required' => true,
        'limited_spot' => true,
        'number_of_spot' => 1,
        'associated_product' => null,
        'image' => null,
        'showOnPortail' => true,
        'location' => 'Salle complete',
        'event_type' => 'in_person',
        'collect_payment' => false,
        'price' => null,
        'tax_rate' => 0,
    ]);
    Reservation::create([
        'event_id' => $fullEvent->id,
        'full_name' => 'Deja inscrit',
        'email' => 'plein@example.test',
    ]);

    $this->actingAs($user)
        ->post("/mobile/evenements/{$fullEvent->id}/participants", [
            'client_profile_id' => $client->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('mobile event actions are protected by ownership and plan', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);
    $starter = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_starter_mensuelle',
    ]);

    $event = Event::create([
        'user_id' => $owner->id,
        'name' => 'Evenement prive',
        'description' => null,
        'start_date_time' => now()->addDays(3),
        'duration' => 60,
        'booking_required' => true,
        'limited_spot' => false,
        'number_of_spot' => null,
        'associated_product' => null,
        'image' => null,
        'showOnPortail' => true,
        'location' => 'Cabinet prive',
        'event_type' => 'in_person',
        'collect_payment' => false,
        'price' => null,
        'tax_rate' => 0,
    ]);

    $this->actingAs($other)
        ->get("/mobile/evenements/{$event->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->get("/mobile/evenements/{$event->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/evenements/{$event->id}", [
            'name' => 'Tentative',
            'start_date_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'duration' => 60,
            'booking_required' => 1,
            'limited_spot' => 0,
            'showOnPortail' => 1,
            'location' => 'Ailleurs',
            'event_type' => 'in_person',
            'collect_payment' => 0,
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/evenements/{$event->id}")
        ->assertForbidden();

    $this->actingAs($starter)
        ->get('/mobile/evenements')
        ->assertOk()
        ->assertSee('offre PRO');

    $this->actingAs($starter)
        ->get('/mobile/evenements/create')
        ->assertForbidden();

    $this->actingAs($starter)
        ->post('/mobile/evenements', [
            'name' => 'Bloque',
            'start_date_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'duration' => 60,
            'booking_required' => 1,
            'limited_spot' => 0,
            'showOnPortail' => 1,
            'location' => 'Cabinet',
            'event_type' => 'in_person',
            'collect_payment' => 0,
        ])
        ->assertForbidden();
});

test('authenticated practitioners can view and create receipt entries from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);

    Receipt::create([
        'user_id' => $user->id,
        'encaissement_date' => '2026-07-01',
        'client_name' => 'Claire Existing',
        'invoice_number' => 'REC-EXISTING',
        'nature' => 'service',
        'amount_ht' => 80,
        'amount_ttc' => 80,
        'payment_method' => 'card',
        'direction' => 'credit',
        'source' => 'manual',
        'note' => 'Deja dans le registre',
    ]);

    $this->actingAs($user)
        ->get('/mobile/recettes')
        ->assertOk()
        ->assertSee('Livre de recettes')
        ->assertSee('Claire Existing')
        ->assertSee('80,00 EUR')
        ->assertSee('CA mensuel');

    $this->actingAs($user)
        ->get('/mobile/recettes/create')
        ->assertOk()
        ->assertSee('Nouvelle ecriture')
        ->assertSee('Registre immuable');

    $this->actingAs($user)
        ->post('/mobile/recettes', [
            'encaissement_date' => '2026-07-02',
            'direction' => 'credit',
            'amount_ttc' => '125.50',
            'amount_ht' => '',
            'payment_method' => 'card',
            'nature' => 'service',
            'client_name' => 'Client mobile',
            'invoice_number' => 'MOB-REC-001',
            'note' => 'Salon mobile',
        ])
        ->assertRedirect(route('mobile.receipts.index', absolute: false))
        ->assertSessionHas('success');

    $receipt = Receipt::where('user_id', $user->id)
        ->where('invoice_number', 'MOB-REC-001')
        ->firstOrFail();

    expect($receipt->record_number)->not->toBeNull();
    expect($receipt->source)->toBe('manual');
    expect((float) $receipt->amount_ttc)->toEqual(125.5);
    expect((float) $receipt->amount_ht)->toEqual(125.5);
});

test('authenticated practitioners can reverse a receipt from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);

    $receipt = Receipt::create([
        'user_id' => $user->id,
        'encaissement_date' => '2026-07-01',
        'client_name' => 'Client correction',
        'invoice_number' => 'REC-CP-001',
        'nature' => 'service',
        'amount_ht' => 100,
        'amount_ttc' => 120,
        'payment_method' => 'transfer',
        'direction' => 'credit',
        'source' => 'manual',
    ]);

    $this->actingAs($user)
        ->from('/mobile/recettes')
        ->post("/mobile/recettes/{$receipt->id}/contre-passer", [
            'encaissement_date' => '2026-07-03',
            'amount_ttc' => '60',
            'note' => 'Annulation partielle',
        ])
        ->assertRedirect('/mobile/recettes')
        ->assertSessionHas('success');

    $reversal = Receipt::where('reversal_of_id', $receipt->id)->firstOrFail();

    expect($reversal->direction)->toBe('debit');
    expect($reversal->source)->toBe('correction');
    expect($reversal->is_reversal)->toBeTrue();
    expect((float) $reversal->amount_ttc)->toEqual(60.0);
    expect((float) $reversal->amount_ht)->toEqual(50.0);
    expect($reversal->note)->toContain('CP de #'.$receipt->id);

    $this->actingAs($user)
        ->from('/mobile/recettes')
        ->post("/mobile/recettes/{$receipt->id}/contre-passer", [
            'encaissement_date' => '2026-07-04',
        ])
        ->assertRedirect('/mobile/recettes')
        ->assertSessionHas('error');

    expect(Receipt::where('reversal_of_id', $receipt->id)->count())->toBe(1);
});

test('mobile receipt monthly report summarizes user receipts', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);

    foreach ([
        ['nature' => 'service', 'direction' => 'credit', 'amount' => 100],
        ['nature' => 'goods', 'direction' => 'credit', 'amount' => 60],
        ['nature' => 'goods', 'direction' => 'debit', 'amount' => 10],
        ['nature' => 'other', 'direction' => 'credit', 'amount' => 20],
    ] as $index => $row) {
        Receipt::create([
            'user_id' => $user->id,
            'encaissement_date' => '2026-02-0'.($index + 1),
            'client_name' => 'Client CA',
            'nature' => $row['nature'],
            'amount_ht' => $row['amount'],
            'amount_ttc' => $row['amount'],
            'payment_method' => 'card',
            'direction' => $row['direction'],
            'source' => 'manual',
        ]);
    }

    $this->actingAs($user)
        ->get('/mobile/recettes/ca-mensuel?year=2026')
        ->assertOk()
        ->assertSee('CA mensuel')
        ->assertSee('Fevrier')
        ->assertSee('170,00 EUR')
        ->assertSee('100,00 EUR')
        ->assertSee('50,00 EUR')
        ->assertSee('20,00 EUR');
});

test('mobile receipt module is gated and protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);
    $starter = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_starter_mensuelle',
    ]);

    $receipt = Receipt::create([
        'user_id' => $owner->id,
        'encaissement_date' => '2026-07-01',
        'client_name' => 'Client secret',
        'nature' => 'service',
        'amount_ht' => 90,
        'amount_ttc' => 90,
        'payment_method' => 'card',
        'direction' => 'credit',
        'source' => 'manual',
    ]);

    $this->actingAs($starter)
        ->get('/mobile/recettes')
        ->assertOk()
        ->assertSee('Module reserve a l offre PRO')
        ->assertDontSee('Client secret');

    $this->actingAs($starter)
        ->get('/mobile/recettes/create')
        ->assertForbidden();

    $this->actingAs($starter)
        ->post('/mobile/recettes', [
            'encaissement_date' => '2026-07-02',
            'direction' => 'credit',
            'amount_ttc' => 30,
            'payment_method' => 'card',
            'nature' => 'service',
        ])
        ->assertForbidden();

    $this->actingAs($starter)
        ->get('/mobile/recettes/ca-mensuel')
        ->assertForbidden();

    $this->actingAs($starter)
        ->post("/mobile/recettes/{$receipt->id}/contre-passer", [
            'encaissement_date' => '2026-07-03',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post("/mobile/recettes/{$receipt->id}/contre-passer", [
            'encaissement_date' => '2026-07-03',
        ])
        ->assertForbidden();

    expect(Receipt::where('reversal_of_id', $receipt->id)->exists())->toBeFalse();
});

test('authenticated practitioners can view and update their profile from mobile', function () {
    $user = User::factory()->create([
        'name' => 'Ancien nom',
        'email' => 'profile-mobile@example.test',
        'email_verified_at' => now(),
        'is_therapist' => true,
        'license_status' => 'active',
        'company_name' => 'Ancien Cabinet',
        'company_email' => 'ancien-cabinet@example.test',
        'company_phone' => '0102030405',
        'company_address' => '1 rue Ancienne',
        'profile_description' => 'Ancienne description',
        'about' => 'Ancien texte',
        'services' => json_encode(['Massage', 'Aromatherapie']),
        'accept_online_appointments' => false,
        'share_address_publicly' => false,
        'share_email_publicly' => false,
        'share_phone_publicly' => false,
        'minimum_notice_hours' => 12,
        'buffer_time_between_appointments' => 10,
        'global_daily_booking_limit' => 4,
        'cancellation_notice_hours' => 24,
    ]);
    $user->slug = User::createUniqueSlug($user->company_name, $user->id);
    $user->save();

    $this->actingAs($user)
        ->get('/mobile/profil')
        ->assertOk()
        ->assertSee('Profil')
        ->assertSee('Ancien Cabinet')
        ->assertSee('Aromatherapie')
        ->assertSee('Modifier');

    $this->actingAs($user)
        ->get('/mobile/profil/edit')
        ->assertOk()
        ->assertSee('Modifier le profil')
        ->assertSee('Prise de RDV')
        ->assertSee('Reglages avances');

    $this->actingAs($user)
        ->put('/mobile/profil', [
            'name' => 'Nouveau nom',
            'email' => 'profile-mobile-new@example.test',
            'company_name' => 'Nouveau Cabinet Mobile',
            'company_email' => 'contact@cabinet-mobile.test',
            'company_phone' => '0601020304',
            'company_address' => '9 rue Mobile',
            'profile_description' => 'Naturopathe mobile',
            'about' => 'Approche douce et concrete.',
            'services_text' => "Aromatherapie\nReflexologie, Aromatherapie",
            'share_address_publicly' => '1',
            'share_email_publicly' => '1',
            'share_phone_publicly' => '1',
            'accept_online_appointments' => '1',
            'minimum_notice_hours' => 6,
            'buffer_time_between_appointments' => 15,
            'global_daily_booking_limit' => 7,
            'cancellation_notice_hours' => 48,
        ])
        ->assertRedirect(route('mobile.profile.index', absolute: false))
        ->assertSessionHas('success');

    $fresh = $user->fresh();

    expect($fresh->name)->toBe('Nouveau nom');
    expect($fresh->email)->toBe('profile-mobile-new@example.test');
    expect($fresh->email_verified_at)->toBeNull();
    expect($fresh->company_name)->toBe('Nouveau Cabinet Mobile');
    expect($fresh->company_address)->toBe('9 rue Mobile');
    expect($fresh->accept_online_appointments)->toBeTrue();
    expect((bool) $fresh->share_address_publicly)->toBeTrue();
    expect((int) $fresh->minimum_notice_hours)->toBe(6);
    expect((int) $fresh->buffer_time_between_appointments)->toBe(15);
    expect((int) $fresh->global_daily_booking_limit)->toBe(7);
    expect((int) $fresh->cancellation_notice_hours)->toBe(48);
    expect(json_decode($fresh->services, true))->toBe(['Aromatherapie', 'Reflexologie']);
    expect($fresh->slug)->toContain('nouveau-cabinet-mobile');
});

test('mobile profile edit is restricted to therapists', function () {
    $user = User::factory()->create([
        'is_therapist' => false,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/profil')
        ->assertOk()
        ->assertSee('Profil');

    $this->actingAs($user)
        ->get('/mobile/profil/edit')
        ->assertForbidden();

    $this->actingAs($user)
        ->put('/mobile/profil', [
            'name' => 'Tentative',
            'email' => $user->email,
        ])
        ->assertForbidden();
});

test('authenticated practitioners can view their subscription from mobile', function () {
    $user = User::factory()->create([
        'email' => 'subscription-pro@example.test',
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
        'stripe_customer_id' => 'cus_mobile_subscription',
    ]);

    $this->actingAs($user)
        ->get('/mobile/abonnement')
        ->assertOk()
        ->assertSee('Abonnement')
        ->assertSee('PRO')
        ->assertSee('new_pro_mensuelle')
        ->assertSee('Actif')
        ->assertSee('Client Stripe')
        ->assertSee('15/17')
        ->assertSee('Factures et devis')
        ->assertSee('Newsletters')
        ->assertSee('Upgrade');
});

test('mobile subscription shows locked modules for starter plans', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_starter_mensuelle',
    ]);

    $this->actingAs($user)
        ->get('/mobile/abonnement')
        ->assertOk()
        ->assertSee('Starter')
        ->assertSee('6/17')
        ->assertSee('Livre de recettes')
        ->assertSee('Bons cadeaux')
        ->assertSee('Upgrade')
        ->assertSee('Voir les offres');
});

test('authenticated practitioners can create a stock item from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/stock/create')
        ->assertOk()
        ->assertSee('Nouvel article')
        ->assertSee('Reference');

    $this->actingAs($user)
        ->post('/mobile/stock', [
            'name' => 'Flacon mobile',
            'reference' => 'MOB-STOCK-001',
            'description' => 'Cree depuis le mobile',
            'price' => 8.5,
            'selling_price' => 14,
            'brand' => 'Olithea',
            'unit_type' => 'unit',
            'quantity_in_stock' => 6,
            'vat_rate_purchase' => 0,
            'vat_rate_sale' => 0,
        ])
        ->assertRedirect(route('mobile.inventory.index', absolute: false));

    $this->assertDatabaseHas('inventory_items', [
        'user_id' => $user->id,
        'name' => 'Flacon mobile',
        'reference' => 'MOB-STOCK-001',
        'unit_type' => 'unit',
        'quantity_in_stock' => 6,
    ]);
});

test('authenticated practitioners can update and delete their stock item from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $item = InventoryItem::create([
        'user_id' => $user->id,
        'name' => 'Ancien stock',
        'reference' => 'MOB-STOCK-EDIT',
        'price' => 5,
        'selling_price' => 10,
        'quantity_in_stock' => 2,
        'unit_type' => 'unit',
    ]);

    $this->actingAs($user)
        ->get("/mobile/stock/{$item->id}/edit")
        ->assertOk()
        ->assertSee('Modifier l article')
        ->assertSee('Ancien stock');

    $this->actingAs($user)
        ->put("/mobile/stock/{$item->id}", [
            'name' => 'Stock modifie',
            'reference' => 'MOB-STOCK-EDIT',
            'description' => 'Mis a jour depuis mobile',
            'price' => 7,
            'price_per_ml' => null,
            'selling_price' => 16,
            'selling_price_per_ml' => null,
            'quantity_in_stock' => 9,
            'brand' => 'Marque mobile',
            'unit_type' => 'unit',
            'quantity_per_unit' => null,
            'quantity_remaining' => null,
            'vat_rate_purchase' => 0,
            'vat_rate_sale' => 5.5,
        ])
        ->assertRedirect(route('mobile.inventory.index', absolute: false));

    $this->assertDatabaseHas('inventory_items', [
        'id' => $item->id,
        'name' => 'Stock modifie',
        'quantity_in_stock' => 9,
        'selling_price' => 16,
        'vat_rate_sale' => 5.5,
    ]);

    $this->actingAs($user)
        ->delete("/mobile/stock/{$item->id}")
        ->assertRedirect(route('mobile.inventory.index', absolute: false));

    $this->assertDatabaseMissing('inventory_items', [
        'id' => $item->id,
    ]);
});

test('authenticated practitioners can consume one stock unit from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $item = InventoryItem::create([
        'user_id' => $user->id,
        'name' => 'Capsule mobile',
        'reference' => 'MOB-STOCK-UNIT',
        'price' => 2,
        'selling_price' => 4,
        'quantity_in_stock' => 3,
        'unit_type' => 'unit',
    ]);

    $this->actingAs($user)
        ->post("/mobile/stock/{$item->id}/consume-unit")
        ->assertRedirect(route('mobile.inventory.index', absolute: false));

    expect((float) $item->fresh()->quantity_in_stock)->toEqual(2.0);
});

test('authenticated practitioners can consume measured stock from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $oil = InventoryItem::create([
        'user_id' => $user->id,
        'name' => 'Huile mobile',
        'reference' => 'MOB-STOCK-ML',
        'price' => 20,
        'price_per_ml' => 0.2,
        'selling_price' => 35,
        'selling_price_per_ml' => 0.35,
        'quantity_in_stock' => 1,
        'unit_type' => 'ml',
        'quantity_per_unit' => 100,
        'quantity_remaining' => 100,
        'drop_to_ml_ratio' => 20,
    ]);

    $powder = InventoryItem::create([
        'user_id' => $user->id,
        'name' => 'Poudre mobile',
        'reference' => 'MOB-STOCK-G',
        'price' => 12,
        'selling_price' => 24,
        'quantity_in_stock' => 1,
        'unit_type' => 'gramme',
        'quantity_per_unit' => 200,
        'quantity_remaining' => 200,
    ]);

    $this->actingAs($user)
        ->post("/mobile/stock/{$oil->id}/consume", [
            'amount_ml' => 12.5,
        ])
        ->assertRedirect(route('mobile.inventory.index', absolute: false));

    $this->actingAs($user)
        ->post("/mobile/stock/{$powder->id}/consume", [
            'amount_gramme' => 15,
        ])
        ->assertRedirect(route('mobile.inventory.index', absolute: false));

    expect((float) $oil->fresh()->quantity_remaining)->toEqual(87.5);
    expect((float) $powder->fresh()->quantity_remaining)->toEqual(185.0);
});

test('mobile stock actions are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $item = InventoryItem::create([
        'user_id' => $owner->id,
        'name' => 'Stock prive',
        'reference' => 'MOB-STOCK-PRIVATE',
        'price' => 5,
        'selling_price' => 8,
        'quantity_in_stock' => 4,
        'unit_type' => 'unit',
    ]);

    $this->actingAs($other)
        ->get("/mobile/stock/{$item->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/stock/{$item->id}", [
            'name' => 'Tentative',
            'reference' => 'MOB-STOCK-PRIVATE',
            'quantity_in_stock' => 2,
            'unit_type' => 'unit',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post("/mobile/stock/{$item->id}/consume-unit")
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/stock/{$item->id}")
        ->assertForbidden();
});

test('authenticated practitioners can create a corporate client from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/entreprises/create')
        ->assertOk()
        ->assertSee('Nouvelle entreprise')
        ->assertSee('Raison sociale');

    $response = $this->actingAs($user)
        ->post('/mobile/entreprises', [
            'name' => 'Entreprise Mobile SAS',
            'trade_name' => 'Mobile B2B',
            'siret' => '12345678900011',
            'vat_number' => 'FR12345678900',
            'billing_address' => '8 rue Corporate',
            'billing_zip' => '75009',
            'billing_city' => 'Paris',
            'billing_country' => 'France',
            'billing_email' => 'facturation.mobile@example.test',
            'billing_phone' => '0102030405',
            'main_contact_first_name' => 'Claire',
            'main_contact_last_name' => 'B2B',
            'main_contact_email' => 'claire.b2b@example.test',
            'main_contact_phone' => '0600000001',
            'notes' => 'Creee depuis mobile',
        ]);

    $company = CorporateClient::where('name', 'Entreprise Mobile SAS')->firstOrFail();

    $response->assertRedirect(route('mobile.corporate-clients.show', $company, absolute: false));

    $this->assertDatabaseHas('corporate_clients', [
        'id' => $company->id,
        'user_id' => $user->id,
        'billing_city' => 'Paris',
        'billing_email' => 'facturation.mobile@example.test',
    ]);
});

test('authenticated practitioners can view corporate client relations from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $company = CorporateClient::create([
        'user_id' => $user->id,
        'name' => 'Societe Detail Mobile',
        'trade_name' => 'Detail B2B',
        'billing_email' => 'detail-billing@example.test',
        'billing_phone' => '0101010101',
        'billing_city' => 'Lyon',
        'main_contact_first_name' => 'Nora',
        'main_contact_last_name' => 'Contact',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'first_name' => 'Camille',
        'last_name' => 'Entreprise',
        'email' => 'camille.entreprise@example.test',
    ]);

    Invoice::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => '2026-07-01',
        'total_amount' => 120,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 120,
        'status' => 'En attente',
        'invoice_number' => 8101,
        'type' => 'invoice',
    ]);

    Invoice::create([
        'user_id' => $user->id,
        'corporate_client_id' => $company->id,
        'invoice_date' => '2026-07-02',
        'total_amount' => 200,
        'total_tax_amount' => 40,
        'total_amount_with_tax' => 240,
        'status' => 'Reglee',
        'invoice_number' => 8102,
        'type' => 'invoice',
    ]);

    $this->actingAs($user)
        ->get("/mobile/entreprises/{$company->id}")
        ->assertOk()
        ->assertSee('Societe Detail Mobile')
        ->assertSee('Nora Contact')
        ->assertSee('Camille Entreprise')
        ->assertSee('Facture 8101')
        ->assertSee('Facture 8102')
        ->assertSee('360,00 EUR');
});

test('authenticated practitioners can update and delete their corporate client from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $company = CorporateClient::create([
        'user_id' => $user->id,
        'name' => 'Ancienne entreprise',
        'billing_email' => 'old@example.test',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'first_name' => 'Client',
        'last_name' => 'Rattache',
    ]);

    $this->actingAs($user)
        ->get("/mobile/entreprises/{$company->id}/edit")
        ->assertOk()
        ->assertSee('Modifier l entreprise')
        ->assertSee('Ancienne entreprise');

    $this->actingAs($user)
        ->put("/mobile/entreprises/{$company->id}", [
            'name' => 'Entreprise modifiee',
            'trade_name' => 'B2B Modifie',
            'siret' => '22222222200022',
            'vat_number' => 'FR22222222222',
            'billing_address' => '12 avenue Update',
            'billing_zip' => '69001',
            'billing_city' => 'Lyon',
            'billing_country' => 'France',
            'billing_email' => 'new@example.test',
            'billing_phone' => '0405060708',
            'main_contact_first_name' => 'Marie',
            'main_contact_last_name' => 'Mobile',
            'main_contact_email' => 'marie.mobile@example.test',
            'main_contact_phone' => '0600000002',
            'notes' => 'Mise a jour mobile',
        ])
        ->assertRedirect(route('mobile.corporate-clients.show', $company, absolute: false));

    $this->assertDatabaseHas('corporate_clients', [
        'id' => $company->id,
        'name' => 'Entreprise modifiee',
        'billing_city' => 'Lyon',
        'billing_email' => 'new@example.test',
    ]);

    $this->actingAs($user)
        ->delete("/mobile/entreprises/{$company->id}")
        ->assertRedirect(route('mobile.corporate-clients.index', absolute: false));

    $this->assertDatabaseMissing('corporate_clients', [
        'id' => $company->id,
    ]);

    expect($client->fresh()->company_id)->toBeNull();
});

test('mobile corporate client actions are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $company = CorporateClient::create([
        'user_id' => $owner->id,
        'name' => 'Entreprise privee',
        'billing_email' => 'private@example.test',
    ]);

    $this->actingAs($other)
        ->get("/mobile/entreprises/{$company->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->get("/mobile/entreprises/{$company->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/entreprises/{$company->id}", [
            'name' => 'Tentative interdite',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/entreprises/{$company->id}")
        ->assertForbidden();
});

test('authenticated practitioners can create an audience from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $camille = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Audience',
        'email' => 'camille.audience@example.test',
    ]);

    $nora = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Nora',
        'last_name' => 'Newsletter',
        'email' => 'nora.newsletter@example.test',
    ]);

    $this->actingAs($user)
        ->get('/mobile/audiences/create')
        ->assertOk()
        ->assertSee('Nouvelle audience')
        ->assertSee('Camille Audience');

    $response = $this->actingAs($user)
        ->post('/mobile/audiences', [
            'name' => 'Clients ateliers mobile',
            'description' => 'Segment cree depuis mobile',
            'client_ids' => [$camille->id, $nora->id],
        ]);

    $audience = Audience::where('name', 'Clients ateliers mobile')->firstOrFail();

    $response->assertRedirect(route('mobile.audiences.show', $audience, absolute: false));

    $this->assertDatabaseHas('audiences', [
        'id' => $audience->id,
        'user_id' => $user->id,
        'description' => 'Segment cree depuis mobile',
    ]);
    $this->assertDatabaseHas('audience_client_profile', [
        'audience_id' => $audience->id,
        'client_profile_id' => $camille->id,
    ]);
    $this->assertDatabaseHas('audience_client_profile', [
        'audience_id' => $audience->id,
        'client_profile_id' => $nora->id,
    ]);
});

test('authenticated practitioners can view audience contacts from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $audience = Audience::create([
        'user_id' => $user->id,
        'name' => 'Audience detail mobile',
        'description' => 'Contacts actifs newsletter',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Claire',
        'last_name' => 'Mobile',
        'email' => 'claire.mobile@example.test',
    ]);

    $audience->clients()->sync([$client->id]);

    $this->actingAs($user)
        ->get("/mobile/audiences/{$audience->id}")
        ->assertOk()
        ->assertSee('Audience detail mobile')
        ->assertSee('Contacts actifs newsletter')
        ->assertSee('Claire Mobile')
        ->assertSee('claire.mobile@example.test')
        ->assertSee('Ouvrir les newsletters');
});

test('authenticated practitioners can update and delete their audience from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $oldClient = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Ancien',
        'last_name' => 'Contact',
    ]);

    $newClient = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Nouveau',
        'last_name' => 'Contact',
    ]);

    $audience = Audience::create([
        'user_id' => $user->id,
        'name' => 'Audience ancienne',
        'description' => 'Avant mobile',
    ]);
    $audience->clients()->sync([$oldClient->id]);

    $this->actingAs($user)
        ->get("/mobile/audiences/{$audience->id}/edit")
        ->assertOk()
        ->assertSee('Modifier l audience')
        ->assertSee('Audience ancienne');

    $this->actingAs($user)
        ->put("/mobile/audiences/{$audience->id}", [
            'name' => 'Audience modifiee',
            'description' => 'Mis a jour depuis mobile',
            'client_ids' => [$newClient->id],
        ])
        ->assertRedirect(route('mobile.audiences.show', $audience, absolute: false));

    $this->assertDatabaseHas('audiences', [
        'id' => $audience->id,
        'name' => 'Audience modifiee',
        'description' => 'Mis a jour depuis mobile',
    ]);
    $this->assertDatabaseMissing('audience_client_profile', [
        'audience_id' => $audience->id,
        'client_profile_id' => $oldClient->id,
    ]);
    $this->assertDatabaseHas('audience_client_profile', [
        'audience_id' => $audience->id,
        'client_profile_id' => $newClient->id,
    ]);

    $this->actingAs($user)
        ->delete("/mobile/audiences/{$audience->id}")
        ->assertRedirect(route('mobile.audiences.index', absolute: false));

    $this->assertDatabaseMissing('audiences', [
        'id' => $audience->id,
    ]);
    $this->assertDatabaseMissing('audience_client_profile', [
        'audience_id' => $audience->id,
        'client_profile_id' => $newClient->id,
    ]);
});

test('mobile audience actions are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $audience = Audience::create([
        'user_id' => $owner->id,
        'name' => 'Audience privee',
    ]);

    $this->actingAs($other)
        ->get("/mobile/audiences/{$audience->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->get("/mobile/audiences/{$audience->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/audiences/{$audience->id}", [
            'name' => 'Tentative interdite',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/audiences/{$audience->id}")
        ->assertForbidden();
});

test('mobile audience form rejects clients from another practitioner', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $ownedClient = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Client',
        'last_name' => 'Autorise',
    ]);

    $otherClient = ClientProfile::create([
        'user_id' => $other->id,
        'first_name' => 'Client',
        'last_name' => 'Hors compte',
    ]);

    $this->actingAs($user)
        ->from('/mobile/audiences/create')
        ->post('/mobile/audiences', [
            'name' => 'Audience interdite',
            'client_ids' => [$otherClient->id],
        ])
        ->assertRedirect('/mobile/audiences/create')
        ->assertSessionHasErrors('client_ids');

    $this->assertDatabaseMissing('audiences', [
        'user_id' => $user->id,
        'name' => 'Audience interdite',
    ]);

    $audience = Audience::create([
        'user_id' => $user->id,
        'name' => 'Audience protegee',
    ]);
    $audience->clients()->sync([$ownedClient->id]);

    $this->actingAs($user)
        ->from("/mobile/audiences/{$audience->id}/edit")
        ->put("/mobile/audiences/{$audience->id}", [
            'name' => 'Audience protegee update',
            'client_ids' => [$otherClient->id],
        ])
        ->assertRedirect("/mobile/audiences/{$audience->id}/edit")
        ->assertSessionHasErrors('client_ids');

    $this->assertDatabaseHas('audience_client_profile', [
        'audience_id' => $audience->id,
        'client_profile_id' => $ownedClient->id,
    ]);
    $this->assertDatabaseMissing('audience_client_profile', [
        'audience_id' => $audience->id,
        'client_profile_id' => $otherClient->id,
    ]);
});

test('authenticated practitioners can create a newsletter from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_premium_mensuelle',
        'name' => 'Cabinet Mobile',
    ]);

    $audience = Audience::create([
        'user_id' => $user->id,
        'name' => 'Ateliers mobile',
        'description' => 'Clients ateliers',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Newsletter',
        'email' => 'camille.newsletter.mobile@example.test',
    ]);
    $audience->clients()->sync([$client->id]);

    $this->actingAs($user)
        ->get('/mobile/newsletters/create')
        ->assertOk()
        ->assertSee('Nouvelle newsletter')
        ->assertSee('Ateliers mobile');

    $response = $this->actingAs($user)
        ->post('/mobile/newsletters', [
            'title' => 'Newsletter mobile juillet',
            'subject' => 'Les nouvelles du cabinet',
            'preheader' => 'Un apercu court',
            'from_name' => 'Cabinet Mobile',
            'background_color' => '#ffffff',
            'audience_id' => $audience->id,
            'heading' => 'Bonjour depuis mobile',
            'body_text' => "Bonjour {{ client.first_name }},\n\nVoici les nouvelles du mois.",
            'image_url' => 'https://example.test/newsletter.jpg',
            'image_alt' => 'Atelier aromatique',
            'button_label' => 'Reserver',
            'button_url' => 'https://example.test/reserver',
            'include_divider' => 1,
        ]);

    $newsletter = Newsletter::where('title', 'Newsletter mobile juillet')->firstOrFail();

    $response->assertRedirect(route('mobile.newsletters.show', $newsletter, absolute: false));

    $this->assertDatabaseHas('newsletters', [
        'id' => $newsletter->id,
        'user_id' => $user->id,
        'subject' => 'Les nouvelles du cabinet',
        'from_email' => 'contact@aromamade.com',
        'audience_id' => $audience->id,
        'status' => 'draft',
    ]);

    $blocks = $newsletter->fresh()->blocks;
    expect($blocks)->toHaveCount(4);
    expect($blocks[0]['type'])->toBe('heading_text');
    expect($blocks[0]['html'])->toContain('Bonjour');
    expect($blocks[1]['type'])->toBe('image');
    expect($blocks[2]['type'])->toBe('divider');
    expect($blocks[3]['type'])->toBe('button');
});

test('authenticated practitioners can view update and delete their newsletter from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_premium_mensuelle',
        'name' => 'Cabinet Update',
    ]);

    $audience = Audience::create([
        'user_id' => $user->id,
        'name' => 'Clients fideles',
    ]);

    $newsletter = Newsletter::create([
        'user_id' => $user->id,
        'title' => 'Ancienne newsletter',
        'subject' => 'Ancien sujet',
        'preheader' => 'Ancien apercu',
        'from_name' => 'Cabinet Update',
        'from_email' => 'contact@aromamade.com',
        'background_color' => '#ffffff',
        'content_json' => json_encode([
            [
                'type' => 'heading_text',
                'heading' => 'Ancien titre',
                'text' => 'Ancien texte',
                'html' => 'Ancien texte',
                'heading_size' => '22px',
                'heading_color' => '#111111',
                'text_size' => '14px',
                'text_color' => '#333333',
                'font_family' => 'Montserrat',
                'text_align' => 'left',
            ],
        ]),
        'status' => 'draft',
        'recipients_count' => 0,
        'audience_id' => $audience->id,
    ]);

    $this->actingAs($user)
        ->get("/mobile/newsletters/{$newsletter->id}")
        ->assertOk()
        ->assertSee('Ancienne newsletter')
        ->assertSee('Ancien sujet')
        ->assertSee('Clients fideles')
        ->assertSee('Envoyer un test');

    $this->actingAs($user)
        ->get("/mobile/newsletters/{$newsletter->id}/edit")
        ->assertOk()
        ->assertSee('Modifier la newsletter')
        ->assertSee('Ancienne newsletter');

    $this->actingAs($user)
        ->put("/mobile/newsletters/{$newsletter->id}", [
            'title' => 'Newsletter modifiee',
            'subject' => 'Sujet modifie',
            'preheader' => 'Apercu modifie',
            'from_name' => 'Cabinet Update',
            'background_color' => '#f7f8f1',
            'audience_id' => '',
            'heading' => 'Titre modifie',
            'body_text' => 'Texte mis a jour depuis mobile',
            'button_label' => 'Lire',
            'button_url' => 'https://example.test/lire',
            'include_divider' => 0,
        ])
        ->assertRedirect(route('mobile.newsletters.show', $newsletter, absolute: false));

    $this->assertDatabaseHas('newsletters', [
        'id' => $newsletter->id,
        'title' => 'Newsletter modifiee',
        'subject' => 'Sujet modifie',
        'audience_id' => null,
        'background_color' => '#f7f8f1',
    ]);

    $blocks = $newsletter->fresh()->blocks;
    expect($blocks)->toHaveCount(2);
    expect($blocks[0]['heading'])->toBe('Titre modifie');
    expect($blocks[1]['type'])->toBe('button');

    $this->actingAs($user)
        ->delete("/mobile/newsletters/{$newsletter->id}")
        ->assertRedirect(route('mobile.newsletters.index', absolute: false));

    $this->assertDatabaseMissing('newsletters', [
        'id' => $newsletter->id,
    ]);
});

test('authenticated practitioners can send newsletter tests and final sends from mobile', function () {
    Mail::fake();

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_premium_mensuelle',
        'name' => 'Cabinet Send',
        'email' => 'cabinet-send@example.test',
    ]);

    $audience = Audience::create([
        'user_id' => $user->id,
        'name' => 'Audience envoi',
    ]);

    $camille = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Send',
        'email' => 'camille.send@example.test',
    ]);
    $nora = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Nora',
        'last_name' => 'Send',
        'email' => 'nora.send@example.test',
    ]);
    $noEmail = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Sans',
        'last_name' => 'Email',
    ]);
    $audience->clients()->sync([$camille->id, $nora->id, $noEmail->id]);

    $newsletter = Newsletter::create([
        'user_id' => $user->id,
        'title' => 'Newsletter a envoyer',
        'subject' => 'Sujet envoi',
        'from_name' => 'Cabinet Send',
        'from_email' => 'contact@aromamade.com',
        'background_color' => '#ffffff',
        'content_json' => json_encode([
            [
                'type' => 'heading_text',
                'heading' => 'Bonjour',
                'text' => 'Message',
                'html' => 'Message',
                'heading_size' => '22px',
                'heading_color' => '#111111',
                'text_size' => '14px',
                'text_color' => '#333333',
                'font_family' => 'Montserrat',
                'text_align' => 'left',
            ],
        ]),
        'status' => 'draft',
        'recipients_count' => 0,
        'audience_id' => $audience->id,
    ]);

    $this->actingAs($user)
        ->post("/mobile/newsletters/{$newsletter->id}/send-test", [
            'test_email' => 'test-newsletter@example.test',
        ])
        ->assertRedirect(route('mobile.newsletters.show', $newsletter, absolute: false))
        ->assertSessionHas('success');

    $this->actingAs($user)
        ->post("/mobile/newsletters/{$newsletter->id}/send-now")
        ->assertRedirect(route('mobile.newsletters.show', $newsletter, absolute: false))
        ->assertSessionHas('success');

    $newsletter->refresh();

    expect($newsletter->status)->toBe('sent');
    expect((int) $newsletter->recipients_count)->toBe(2);
    expect(NewsletterRecipient::where('newsletter_id', $newsletter->id)->count())->toBe(2);

    $this->assertDatabaseHas('newsletter_recipients', [
        'newsletter_id' => $newsletter->id,
        'client_profile_id' => $camille->id,
        'email' => 'camille.send@example.test',
        'status' => 'sent',
    ]);
    $this->assertDatabaseHas('newsletter_recipients', [
        'newsletter_id' => $newsletter->id,
        'client_profile_id' => $nora->id,
        'email' => 'nora.send@example.test',
        'status' => 'sent',
    ]);
    $this->assertDatabaseMissing('newsletter_recipients', [
        'newsletter_id' => $newsletter->id,
        'client_profile_id' => $noEmail->id,
    ]);
    $this->assertDatabaseHas('newsletter_monthly_usages', [
        'user_id' => $user->id,
        'month' => now()->format('Y-m'),
        'sent_count' => 2,
    ]);
});

test('mobile newsletter actions are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_premium_mensuelle',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_premium_mensuelle',
    ]);

    $newsletter = Newsletter::create([
        'user_id' => $owner->id,
        'title' => 'Newsletter privee',
        'subject' => 'Sujet prive',
        'from_name' => 'Owner',
        'from_email' => 'contact@aromamade.com',
        'background_color' => '#ffffff',
        'content_json' => json_encode([
            ['type' => 'heading_text', 'heading' => 'Prive', 'html' => 'Prive'],
        ]),
        'status' => 'draft',
        'recipients_count' => 0,
    ]);

    $this->actingAs($other)
        ->get("/mobile/newsletters/{$newsletter->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->get("/mobile/newsletters/{$newsletter->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/newsletters/{$newsletter->id}", [
            'title' => 'Tentative',
            'subject' => 'Tentative',
            'from_name' => 'Other',
            'background_color' => '#ffffff',
            'body_text' => 'Tentative',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post("/mobile/newsletters/{$newsletter->id}/send-test", [
            'test_email' => 'other@example.test',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post("/mobile/newsletters/{$newsletter->id}/send-now")
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/newsletters/{$newsletter->id}")
        ->assertForbidden();
});

test('mobile newsletter form rejects audiences from another practitioner', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_premium_mensuelle',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_premium_mensuelle',
    ]);

    $otherAudience = Audience::create([
        'user_id' => $other->id,
        'name' => 'Audience hors compte',
    ]);

    $this->actingAs($user)
        ->from('/mobile/newsletters/create')
        ->post('/mobile/newsletters', [
            'title' => 'Newsletter interdite',
            'subject' => 'Sujet interdit',
            'from_name' => 'Cabinet',
            'background_color' => '#ffffff',
            'audience_id' => $otherAudience->id,
            'body_text' => 'Message',
        ])
        ->assertRedirect('/mobile/newsletters/create')
        ->assertSessionHasErrors('audience_id');

    $this->assertDatabaseMissing('newsletters', [
        'user_id' => $user->id,
        'title' => 'Newsletter interdite',
    ]);
});

test('mobile newsletter feature is locked for non premium modern plans', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_starter_mensuelle',
    ]);

    $this->actingAs($user)
        ->get('/mobile/newsletters')
        ->assertOk()
        ->assertSee('Module newsletter reserve Premium');

    $this->actingAs($user)
        ->get('/mobile/newsletters/create')
        ->assertForbidden();

    $this->actingAs($user)
        ->post('/mobile/newsletters', [
            'title' => 'Newsletter bloquee',
            'subject' => 'Sujet bloque',
            'from_name' => 'Starter',
            'background_color' => '#ffffff',
            'body_text' => 'Message',
        ])
        ->assertForbidden();
});

test('authenticated therapists can view referral dashboard from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'name' => 'Therapeute Parrain',
    ]);

    $signedUp = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $signedUp->forceFill([
        'referred_by_user_id' => $user->id,
        'referral_attributed_at' => now(),
    ])->save();

    $paid = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $paid->forceFill([
        'referred_by_user_id' => $user->id,
        'referral_attributed_at' => now(),
        'referral_converted_at' => now(),
    ])->save();

    ReferralInvite::create([
        'referrer_user_id' => $user->id,
        'email' => 'invite-mobile@example.test',
        'token' => 'mobile-referral-token-view',
        'status' => 'opened',
        'message' => 'Invitation mobile',
        'expires_at' => now()->addDays(20),
    ]);

    $this->actingAs($user)
        ->get('/mobile/parrainage')
        ->assertOk()
        ->assertSee('Parrainage')
        ->assertSee('register-pro?ref=', false)
        ->assertSee('invite-mobile@example.test')
        ->assertSee('Ouverte')
        ->assertSee('Inscrits')
        ->assertSee('Payants');

    expect(ReferralCode::where('user_id', $user->id)->exists())->toBeTrue();
});

test('authenticated therapists can send referral invites from mobile', function () {
    Mail::fake();

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'name' => 'Mobile Parrain',
    ]);

    $this->actingAs($user)
        ->post('/mobile/parrainage/invitations', [
            'email' => 'New-Therapist@Example.TEST',
            'message' => 'Je te recommande AromaMade PRO.',
        ])
        ->assertRedirect(route('mobile.referrals.index', absolute: false))
        ->assertSessionHas('success');

    $invite = ReferralInvite::where('email', 'new-therapist@example.test')->firstOrFail();
    $code = ReferralCode::where('user_id', $user->id)->firstOrFail();

    $this->assertDatabaseHas('referral_invites', [
        'id' => $invite->id,
        'referrer_user_id' => $user->id,
        'email' => 'new-therapist@example.test',
        'status' => 'sent',
        'message' => 'Je te recommande AromaMade PRO.',
    ]);

    expect($invite->expires_at)->not()->toBeNull();
    expect($code->code)->not()->toBeEmpty();

    Mail::assertSent(TherapistInviteMail::class, function (TherapistInviteMail $mail) use ($invite, $user, $code) {
        return $mail->hasTo('new-therapist@example.test')
            && $mail->invite->is($invite)
            && $mail->referrer->is($user)
            && str_contains($mail->signupUrl, $invite->token)
            && str_contains($mail->signupUrl, $code->code);
    });
});

test('mobile referral invite avoids recent duplicates and daily spam', function () {
    Mail::fake();

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    ReferralInvite::create([
        'referrer_user_id' => $user->id,
        'email' => 'recent@example.test',
        'token' => 'recent-referral-token',
        'status' => 'sent',
        'expires_at' => now()->addDays(20),
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);

    $this->actingAs($user)
        ->post('/mobile/parrainage/invitations', [
            'email' => 'recent@example.test',
        ])
        ->assertRedirect(route('mobile.referrals.index', absolute: false))
        ->assertSessionHas('success');

    expect(ReferralInvite::where('email', 'recent@example.test')->count())->toBe(1);

    for ($i = 0; $i < 20; $i++) {
        ReferralInvite::create([
            'referrer_user_id' => $user->id,
            'email' => "today-{$i}@example.test",
            'token' => "today-referral-token-{$i}",
            'status' => 'sent',
            'expires_at' => now()->addDays(20),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->actingAs($user)
        ->from('/mobile/parrainage')
        ->post('/mobile/parrainage/invitations', [
            'email' => 'too-many@example.test',
        ])
        ->assertRedirect('/mobile/parrainage')
        ->assertSessionHasErrors('email');

    $this->assertDatabaseMissing('referral_invites', [
        'email' => 'too-many@example.test',
    ]);
});

test('authenticated therapists can resend referral invites from mobile', function () {
    Mail::fake();

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $invite = ReferralInvite::create([
        'referrer_user_id' => $user->id,
        'email' => 'resend@example.test',
        'token' => 'resend-referral-token',
        'status' => 'opened',
        'expires_at' => now()->addDays(10),
    ]);

    $this->actingAs($user)
        ->post("/mobile/parrainage/invitations/{$invite->id}/renvoyer")
        ->assertRedirect(route('mobile.referrals.index', absolute: false))
        ->assertSessionHas('success');

    Mail::assertSent(TherapistInviteMail::class, function (TherapistInviteMail $mail) use ($invite) {
        return $mail->hasTo('resend@example.test')
            && $mail->invite->is($invite)
            && str_contains($mail->signupUrl, $invite->token);
    });
});

test('mobile referral resend is protected by ownership and expiration', function () {
    Mail::fake();

    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $invite = ReferralInvite::create([
        'referrer_user_id' => $owner->id,
        'email' => 'private-referral@example.test',
        'token' => 'private-referral-token',
        'status' => 'sent',
        'expires_at' => now()->addDays(10),
    ]);

    $this->actingAs($other)
        ->post("/mobile/parrainage/invitations/{$invite->id}/renvoyer")
        ->assertForbidden();

    $expired = ReferralInvite::create([
        'referrer_user_id' => $owner->id,
        'email' => 'expired-referral@example.test',
        'token' => 'expired-referral-token',
        'status' => 'sent',
        'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($owner)
        ->post("/mobile/parrainage/invitations/{$expired->id}/renvoyer")
        ->assertRedirect(route('mobile.referrals.index', absolute: false))
        ->assertSessionHasErrors('email');
});

test('mobile referral module is restricted to therapists', function () {
    $user = User::factory()->create([
        'is_therapist' => false,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/parrainage')
        ->assertForbidden();

    $this->actingAs($user)
        ->post('/mobile/parrainage/invitations', [
            'email' => 'blocked@example.test',
        ])
        ->assertForbidden();
});

test('authenticated practitioners can create a prestation from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/prestations/create')
        ->assertOk()
        ->assertSee('Nouvelle prestation');

    $response = $this->actingAs($user)
        ->post('/mobile/prestations', [
            'name' => 'Consultation mobile',
            'description' => 'Creee depuis le mobile',
            'price' => 75,
            'tax_rate' => 0,
            'duration' => 60,
            'mode' => 'visio',
            'can_be_booked_online' => 1,
            'collect_payment' => 1,
            'requires_emargement' => 0,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 1,
            'display_order' => 2,
        ]);

    $product = Product::where('user_id', $user->id)->where('name', 'Consultation mobile')->firstOrFail();

    $response->assertRedirect(route('mobile.products.show', $product, false));

    $this->assertDatabaseHas('products', [
        'user_id' => $user->id,
        'name' => 'Consultation mobile',
        'price' => 75,
        'duration' => 60,
        'visio' => true,
        'can_be_booked_online' => true,
        'collect_payment' => true,
    ]);

    $this->actingAs($user)
        ->get(route('mobile.products.index', absolute: false))
        ->assertOk()
        ->assertSee('Prestations')
        ->assertSee('Consultation mobile')
        ->assertSee('75,00 EUR');

    $this->actingAs($user)
        ->get(route('mobile.products.show', $product, false))
        ->assertOk()
        ->assertSee('Consultation mobile')
        ->assertSee('Reservation')
        ->assertSee('Automatisations');
});

test('authenticated practitioners can update their prestation from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Ancienne prestation',
        'price' => 45,
        'tax_rate' => 0,
        'duration' => 45,
        'can_be_booked_online' => false,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => true,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $this->actingAs($user)
        ->get("/mobile/prestations/{$product->id}/edit")
        ->assertOk()
        ->assertSee('Modifier la prestation');

    $this->actingAs($user)
        ->put("/mobile/prestations/{$product->id}", [
            'name' => 'Prestation modifiee',
            'description' => 'Depuis mobile',
            'price' => 95,
            'tax_rate' => 5.5,
            'duration' => 75,
            'mode' => 'dans_le_cabinet',
            'can_be_booked_online' => 1,
            'collect_payment' => 0,
            'requires_emargement' => 1,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 0,
            'display_order' => 4,
        ])
        ->assertRedirect(route('mobile.products.show', $product, false));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Prestation modifiee',
        'price' => 95,
        'tax_rate' => 5.5,
        'duration' => 75,
        'dans_le_cabinet' => true,
        'requires_emargement' => true,
        'price_visible_in_portal' => false,
    ]);

    $this->actingAs($user)
        ->get(route('mobile.products.show', $product, false))
        ->assertOk()
        ->assertSee('Prestation modifiee')
        ->assertSee('Emargement requis');

    $this->actingAs($user)
        ->delete(route('mobile.products.destroy', $product, false))
        ->assertRedirect(route('mobile.products.index', absolute: false));

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

test('mobile prestation actions are protected by ownership and active license', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $inactive = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'inactive',
    ]);

    $product = Product::create([
        'user_id' => $owner->id,
        'name' => 'Prestation privee',
        'price' => 45,
        'tax_rate' => 0,
        'duration' => 45,
        'can_be_booked_online' => false,
        'collect_payment' => false,
        'visio' => true,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $this->actingAs($inactive)
        ->get(route('mobile.products.index', absolute: false))
        ->assertRedirect('/license-tiers/pricing');

    $this->actingAs($other)
        ->get(route('mobile.products.show', $product, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->get("/mobile/prestations/{$product->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/prestations/{$product->id}", [
            'name' => 'Tentative',
            'price' => 99,
            'tax_rate' => 0,
            'duration' => 30,
            'mode' => 'visio',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('mobile.products.destroy', $product, false))
        ->assertForbidden();
});

test('authenticated practitioners can create a client from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $company = CorporateClient::create([
        'user_id' => $user->id,
        'name' => 'Entreprise Mobile',
    ]);

    $this->actingAs($user)
        ->get('/mobile/clients/create')
        ->assertOk()
        ->assertSee('Nouvelle fiche client')
        ->assertSee('Entreprise rattachee');

    $response = $this->actingAs($user)
        ->post('/mobile/clients', [
            'first_name' => 'Camille',
            'last_name' => 'Martin',
            'email' => 'camille.mobile@example.test',
            'phone' => '0601020304',
            'birthdate' => '1991-04-12',
            'address' => '12 rue Mobile',
            'notes' => 'Cree depuis mobile',
            'first_name_billing' => 'Camille',
            'last_name_billing' => 'Martin Pro',
            'company_id' => $company->id,
        ]);

    $client = ClientProfile::where('email', 'camille.mobile@example.test')->firstOrFail();

    $response->assertRedirect(route('mobile.clients.show', $client, absolute: false));

    $this->assertDatabaseHas('client_profiles', [
        'id' => $client->id,
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Martin',
        'company_id' => $company->id,
    ]);
});

test('authenticated practitioners can update their client from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Ancien',
        'last_name' => 'Client',
        'email' => 'ancien@example.test',
    ]);

    $this->actingAs($user)
        ->get("/mobile/clients/{$client->id}/edit")
        ->assertOk()
        ->assertSee('Modifier la fiche client')
        ->assertSee('Ancien');

    $this->actingAs($user)
        ->put("/mobile/clients/{$client->id}", [
            'first_name' => 'Nouveau',
            'last_name' => 'Client',
            'email' => 'nouveau@example.test',
            'phone' => '0611223344',
            'birthdate' => '1988-09-30',
            'address' => '4 avenue Mobile',
            'notes' => 'Mis a jour depuis mobile',
            'first_name_billing' => 'Facture',
            'last_name_billing' => 'Mobile',
        ])
        ->assertRedirect(route('mobile.clients.show', $client, absolute: false));

    $this->assertDatabaseHas('client_profiles', [
        'id' => $client->id,
        'first_name' => 'Nouveau',
        'email' => 'nouveau@example.test',
        'phone' => '0611223344',
        'last_name_billing' => 'Mobile',
    ]);
});

test('mobile client edit is protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Client',
        'last_name' => 'Prive',
    ]);

    $this->actingAs($other)
        ->get("/mobile/clients/{$client->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/clients/{$client->id}", [
            'first_name' => 'Tentative',
            'last_name' => 'Interdite',
        ])
        ->assertForbidden();
});

test('mobile client form rejects companies from another practitioner', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $otherCompany = CorporateClient::create([
        'user_id' => $other->id,
        'name' => 'Entreprise hors compte',
    ]);

    $this->actingAs($user)
        ->from('/mobile/clients/create')
        ->post('/mobile/clients', [
            'first_name' => 'Client',
            'last_name' => 'Mobile',
            'company_id' => $otherCompany->id,
        ])
        ->assertRedirect('/mobile/clients/create')
        ->assertSessionHasErrors('company_id');

    $this->assertDatabaseMissing('client_profiles', [
        'user_id' => $user->id,
        'first_name' => 'Client',
        'last_name' => 'Mobile',
        'company_id' => $otherCompany->id,
    ]);
});

test('authenticated practitioners can open invoice and quote details from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Iris',
        'last_name' => 'Facture',
        'email' => 'iris.facture@example.test',
    ]);

    $invoice = Invoice::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => '2026-07-01',
        'due_date' => '2026-07-15',
        'total_amount' => 100,
        'total_tax_amount' => 20,
        'total_amount_with_tax' => 120,
        'status' => 'En attente',
        'invoice_number' => 7001,
        'type' => 'invoice',
        'notes' => 'Note mobile facture',
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'type' => 'custom',
        'description' => 'Consultation mobile',
        'quantity' => 1,
        'unit_price' => 100,
        'tax_rate' => 20,
        'tax_amount' => 20,
        'total_price' => 100,
        'total_price_with_tax' => 120,
    ]);

    $quote = Invoice::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => '2026-07-02',
        'due_date' => '2026-07-20',
        'total_amount' => 80,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 80,
        'status' => 'Devis',
        'quote_number' => 'DEV-MOB-001',
        'type' => 'quote',
    ]);

    InvoiceItem::create([
        'invoice_id' => $quote->id,
        'type' => 'custom',
        'description' => 'Proposition mobile',
        'quantity' => 2,
        'unit_price' => 40,
        'tax_rate' => 0,
        'tax_amount' => 0,
        'total_price' => 80,
        'total_price_with_tax' => 80,
    ]);

    $this->actingAs($user)
        ->get('/mobile/invoices')
        ->assertOk()
        ->assertSee('/mobile/invoices/' . $invoice->id, false)
        ->assertSee('/mobile/devis/' . $quote->id, false);

    $this->actingAs($user)
        ->get("/mobile/invoices/{$invoice->id}")
        ->assertOk()
        ->assertSee('7001')
        ->assertSee('Iris Facture')
        ->assertSee('Consultation mobile')
        ->assertSee('Solde restant');

    $this->actingAs($user)
        ->get("/mobile/devis/{$quote->id}")
        ->assertOk()
        ->assertSee('DEV-MOB-001')
        ->assertSee('Proposition mobile')
        ->assertSee('Ouvrir la vue web complete');
});

test('mobile invoice details are protected by ownership and document type', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Client',
        'last_name' => 'Prive',
    ]);

    $invoice = Invoice::create([
        'user_id' => $owner->id,
        'client_profile_id' => $client->id,
        'invoice_date' => '2026-07-01',
        'total_amount' => 50,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 50,
        'status' => 'En attente',
        'invoice_number' => 7002,
        'type' => 'invoice',
    ]);

    $quote = Invoice::create([
        'user_id' => $owner->id,
        'client_profile_id' => $client->id,
        'invoice_date' => '2026-07-01',
        'total_amount' => 50,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 50,
        'status' => 'Devis',
        'quote_number' => 'DEV-PRIVATE',
        'type' => 'quote',
    ]);

    $this->actingAs($other)
        ->get("/mobile/invoices/{$invoice->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->get("/mobile/devis/{$quote->id}")
        ->assertForbidden();

    $this->actingAs($owner)
        ->get("/mobile/invoices/{$quote->id}")
        ->assertNotFound();
});

test('authenticated practitioners can create an appointment from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Alice',
        'last_name' => 'Rdv',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Soin domicile',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 45,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => true,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $this->actingAs($user)
        ->get('/mobile/rendez-vous/create?client_profile_id=' . $client->id)
        ->assertOk()
        ->assertSee('Nouveau rendez-vous')
        ->assertSee('Alice Rdv')
        ->assertSee('Soin domicile');

    $response = $this->actingAs($user)
        ->post('/mobile/rendez-vous', [
            'client_profile_id' => $client->id,
            'product_id' => $product->id,
            'appointment_date' => now()->subDay()->format('Y-m-d'),
            'appointment_time' => '09:30',
            'type' => 'domicile',
            'status' => 'Programme',
            'notes' => 'Cree depuis mobile',
            'force_availability_override' => 1,
        ]);

    $appointment = Appointment::where('user_id', $user->id)
        ->where('notes', 'Cree depuis mobile')
        ->firstOrFail();

    $response->assertRedirect(route('mobile.appointments.show', $appointment, absolute: false));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'duration' => 45,
        'type' => 'domicile',
        'status' => 'Programme',
    ]);
});

test('mobile appointments default to the first useful tab', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Avenir',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Soin a venir',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => true,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->addDays(2)->setTime(10, 30),
        'duration' => 60,
        'type' => 'cabinet',
        'status' => 'Programme',
    ]);

    $this->actingAs($user)
        ->get(route('mobile.appointments.index', absolute: false))
        ->assertOk()
        ->assertSee("x-data=\"{ tab: 'upcoming' }\"", false)
        ->assertSee('Camille Avenir');

    $this->actingAs($user)
        ->get(route('mobile.appointments.index', ['filter' => 'today'], false))
        ->assertOk()
        ->assertSee("x-data=\"{ tab: 'today' }\"", false);
});

test('authenticated practitioners can update their appointment from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Bruno',
        'last_name' => 'Mobile',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Soin entreprise',
        'price' => 120,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => false,
        'en_entreprise' => true,
        'dans_le_cabinet' => false,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $appointment = Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->subDays(2)->setTime(10, 0),
        'duration' => 60,
        'type' => 'entreprise',
        'status' => 'Programme',
        'notes' => 'Ancienne note',
    ]);

    $this->actingAs($user)
        ->get("/mobile/rendez-vous/{$appointment->id}/edit")
        ->assertOk()
        ->assertSee('Modifier le rendez-vous')
        ->assertSee('Bruno Mobile');

    $this->actingAs($user)
        ->put("/mobile/rendez-vous/{$appointment->id}", [
            'client_profile_id' => $client->id,
            'product_id' => $product->id,
            'appointment_date' => now()->subDay()->format('Y-m-d'),
            'appointment_time' => '14:15',
            'type' => 'entreprise',
            'status' => 'Confirme',
            'notes' => 'Mis a jour depuis mobile',
            'force_availability_override' => 1,
        ])
        ->assertRedirect(route('mobile.appointments.show', $appointment, absolute: false));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => 'Confirme',
        'notes' => 'Mis a jour depuis mobile',
        'duration' => 60,
    ]);
});

test('mobile appointment edit is protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Client',
        'last_name' => 'Prive',
    ]);

    $product = Product::create([
        'user_id' => $owner->id,
        'name' => 'Prestation privee',
        'price' => 50,
        'tax_rate' => 0,
        'duration' => 30,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => true,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $appointment = Appointment::create([
        'user_id' => $owner->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->subDay()->setTime(8, 0),
        'duration' => 30,
        'type' => 'domicile',
        'status' => 'Programme',
    ]);

    $this->actingAs($other)
        ->get("/mobile/rendez-vous/{$appointment->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/rendez-vous/{$appointment->id}", [
            'client_profile_id' => $client->id,
            'product_id' => $product->id,
            'appointment_date' => now()->subDay()->format('Y-m-d'),
            'appointment_time' => '11:00',
            'type' => 'domicile',
            'status' => 'Confirme',
            'force_availability_override' => 1,
        ])
        ->assertForbidden();
});

test('authenticated practitioners can create an availability from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Soin cabinet mobile',
        'price' => 70,
        'tax_rate' => 0,
        'duration' => 45,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => true,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $location = PracticeLocation::create([
        'user_id' => $user->id,
        'label' => 'Cabinet Mobile',
        'address_line1' => '1 rue Mobile',
        'postal_code' => '75001',
        'city' => 'Paris',
        'country' => 'FR',
        'is_primary' => true,
    ]);

    $this->actingAs($user)
        ->get('/mobile/disponibilites/create')
        ->assertOk()
        ->assertSee('Nouvelle disponibilite')
        ->assertSee('Soin cabinet mobile')
        ->assertSee('Cabinet Mobile');

    $response = $this->actingAs($user)
        ->post('/mobile/disponibilites', [
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'applies_to_all' => 0,
            'products' => [$product->id],
            'practice_location_id' => $location->id,
        ]);

    $availability = Availability::where('user_id', $user->id)->firstOrFail();

    $response->assertRedirect(route('mobile.availabilities.index', absolute: false));

    $this->assertDatabaseHas('availabilities', [
        'id' => $availability->id,
        'user_id' => $user->id,
        'day_of_week' => 1,
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'applies_to_all' => false,
        'practice_location_id' => $location->id,
    ]);

    $this->assertDatabaseHas('availability_product', [
        'availability_id' => $availability->id,
        'product_id' => $product->id,
    ]);
});

test('authenticated practitioners can update and delete their availability from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Soin cible',
        'price' => 55,
        'tax_rate' => 0,
        'duration' => 30,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => true,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $availability = Availability::create([
        'user_id' => $user->id,
        'day_of_week' => 0,
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
        'applies_to_all' => true,
    ]);

    $this->actingAs($user)
        ->get("/mobile/disponibilites/{$availability->id}/edit")
        ->assertOk()
        ->assertSee('Modifier la disponibilite');

    $this->actingAs($user)
        ->put("/mobile/disponibilites/{$availability->id}", [
            'day_of_week' => 2,
            'start_time' => '14:00',
            'end_time' => '17:30',
            'applies_to_all' => 0,
            'products' => [$product->id],
        ])
        ->assertRedirect(route('mobile.availabilities.index', absolute: false));

    $this->assertDatabaseHas('availabilities', [
        'id' => $availability->id,
        'day_of_week' => 2,
        'start_time' => '14:00:00',
        'end_time' => '17:30:00',
        'applies_to_all' => false,
    ]);

    $this->assertDatabaseHas('availability_product', [
        'availability_id' => $availability->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($user)
        ->delete("/mobile/disponibilites/{$availability->id}")
        ->assertRedirect(route('mobile.availabilities.index', absolute: false));

    $this->assertDatabaseMissing('availabilities', [
        'id' => $availability->id,
    ]);
});

test('mobile availability edit is protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $availability = Availability::create([
        'user_id' => $owner->id,
        'day_of_week' => 3,
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
        'applies_to_all' => true,
    ]);

    $this->actingAs($other)
        ->get("/mobile/disponibilites/{$availability->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/disponibilites/{$availability->id}", [
            'day_of_week' => 3,
            'start_time' => '12:00',
            'end_time' => '13:00',
            'applies_to_all' => 1,
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/disponibilites/{$availability->id}")
        ->assertForbidden();
});

test('authenticated practitioners can create a practice location from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->mock(FrenchAddressGeocodingService::class, function ($mock) {
        $mock->shouldReceive('geocodeAddressParts')
            ->once()
            ->andReturn(['latitude' => 48.8566, 'longitude' => 2.3522]);
    });

    $this->actingAs($user)
        ->get('/mobile/lieux/create')
        ->assertOk()
        ->assertSee('Nouveau lieu');

    $this->actingAs($user)
        ->post('/mobile/lieux', [
            'label' => 'Cabinet mobile',
            'address_line1' => '10 rue du Mobile',
            'address_line2' => 'Etage 2',
            'postal_code' => '75002',
            'city' => 'Paris',
            'country' => 'FR',
            'is_primary' => 1,
            'is_shared' => 0,
        ])
        ->assertRedirect(route('mobile.practice-locations.index', absolute: false));

    $this->assertDatabaseHas('practice_locations', [
        'user_id' => $user->id,
        'label' => 'Cabinet mobile',
        'address_line1' => '10 rue du Mobile',
        'postal_code' => '75002',
        'city' => 'Paris',
        'country' => 'FR',
        'is_primary' => true,
        'is_shared' => false,
    ]);
});

test('authenticated practitioners can update and delete their practice location from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $location = PracticeLocation::create([
        'user_id' => $user->id,
        'label' => 'Ancien cabinet',
        'address_line1' => '1 rue Ancienne',
        'postal_code' => '69001',
        'city' => 'Lyon',
        'country' => 'FR',
        'is_primary' => true,
    ]);

    $this->mock(FrenchAddressGeocodingService::class, function ($mock) {
        $mock->shouldReceive('geocodeAddressParts')
            ->once()
            ->andReturn(['latitude' => 45.764, 'longitude' => 4.8357]);
    });

    $this->actingAs($user)
        ->get("/mobile/lieux/{$location->id}/edit")
        ->assertOk()
        ->assertSee('Modifier le lieu')
        ->assertSee('Ancien cabinet');

    $this->actingAs($user)
        ->put("/mobile/lieux/{$location->id}", [
            'label' => 'Cabinet modifie',
            'address_line1' => '2 rue Nouvelle',
            'postal_code' => '69002',
            'city' => 'Lyon',
            'country' => 'FR',
            'is_primary' => 0,
            'is_shared' => 0,
        ])
        ->assertRedirect(route('mobile.practice-locations.index', absolute: false));

    $this->assertDatabaseHas('practice_locations', [
        'id' => $location->id,
        'label' => 'Cabinet modifie',
        'address_line1' => '2 rue Nouvelle',
        'postal_code' => '69002',
        'is_primary' => false,
    ]);

    $this->actingAs($user)
        ->delete("/mobile/lieux/{$location->id}")
        ->assertRedirect(route('mobile.practice-locations.index', absolute: false));

    $this->assertDatabaseMissing('practice_locations', [
        'id' => $location->id,
    ]);
});

test('mobile practice location edit is protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $location = PracticeLocation::create([
        'user_id' => $owner->id,
        'label' => 'Cabinet prive',
        'address_line1' => '3 rue Privee',
        'postal_code' => '33000',
        'city' => 'Bordeaux',
        'country' => 'FR',
        'is_primary' => true,
    ]);

    $this->actingAs($other)
        ->get("/mobile/lieux/{$location->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/lieux/{$location->id}", [
            'label' => 'Cabinet vole',
            'address_line1' => '4 rue Non',
            'postal_code' => '33000',
            'city' => 'Bordeaux',
            'country' => 'FR',
            'is_primary' => 0,
            'is_shared' => 0,
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/lieux/{$location->id}")
        ->assertForbidden();
});

test('authenticated practitioners can create a pack from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $consultation = Product::create([
        'user_id' => $user->id,
        'name' => 'Consultation pack mobile',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
    ]);

    $massage = Product::create([
        'user_id' => $user->id,
        'name' => 'Massage pack mobile',
        'price' => 60,
        'tax_rate' => 0,
        'duration' => 45,
    ]);

    $this->actingAs($user)
        ->get('/mobile/packs/create')
        ->assertOk()
        ->assertSee('Nouveau pack')
        ->assertSee('Contenu du pack');

    $response = $this->actingAs($user)
        ->post('/mobile/packs', [
            'name' => 'Pack mobile',
            'description' => 'Cree depuis mobile',
            'price' => 180,
            'tax_rate' => 20,
            'is_active' => 1,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 1,
            'installments_enabled' => 1,
            'allowed_installments' => [2, 3],
            'items' => [
                ['product_id' => $consultation->id, 'quantity' => 3],
                ['product_id' => $massage->id, 'quantity' => 1],
            ],
        ]);

    $pack = PackProduct::where('name', 'Pack mobile')->firstOrFail();

    $response->assertRedirect(route('mobile.packs.show', $pack, absolute: false));

    expect($pack->fresh()->allowed_installments)->toBe([2, 3]);

    $this->assertDatabaseHas('pack_products', [
        'id' => $pack->id,
        'user_id' => $user->id,
        'price' => 180,
        'tax_rate' => 20,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
        'installments_enabled' => true,
    ]);

    $this->assertDatabaseHas('pack_product_items', [
        'pack_product_id' => $pack->id,
        'product_id' => $consultation->id,
        'quantity' => 3,
        'sort_order' => 0,
    ]);
    $this->assertDatabaseHas('pack_product_items', [
        'pack_product_id' => $pack->id,
        'product_id' => $massage->id,
        'quantity' => 1,
        'sort_order' => 1,
    ]);
});

test('authenticated practitioners can update and delete their pack from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $oldProduct = Product::create([
        'user_id' => $user->id,
        'name' => 'Ancienne prestation pack',
        'price' => 50,
        'tax_rate' => 0,
        'duration' => 45,
    ]);

    $newProduct = Product::create([
        'user_id' => $user->id,
        'name' => 'Nouvelle prestation pack',
        'price' => 90,
        'tax_rate' => 0,
        'duration' => 75,
    ]);

    $pack = PackProduct::create([
        'user_id' => $user->id,
        'name' => 'Ancien pack',
        'description' => 'Avant mobile',
        'price' => 100,
        'tax_rate' => 0,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
        'installments_enabled' => true,
        'allowed_installments' => [2],
    ]);

    PackProductItem::create([
        'pack_product_id' => $pack->id,
        'product_id' => $oldProduct->id,
        'quantity' => 2,
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->get("/mobile/packs/{$pack->id}/edit")
        ->assertOk()
        ->assertSee('Modifier le pack')
        ->assertSee('Ancien pack');

    $this->actingAs($user)
        ->put("/mobile/packs/{$pack->id}", [
            'name' => 'Pack modifie',
            'description' => 'Mis a jour depuis mobile',
            'price' => 210,
            'tax_rate' => 5.5,
            'is_active' => 0,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 0,
            'installments_enabled' => 0,
            'items' => [
                ['product_id' => $newProduct->id, 'quantity' => 4],
            ],
        ])
        ->assertRedirect(route('mobile.packs.show', $pack, absolute: false));

    $this->assertDatabaseHas('pack_products', [
        'id' => $pack->id,
        'name' => 'Pack modifie',
        'price' => 210,
        'tax_rate' => 5.5,
        'is_active' => false,
        'price_visible_in_portal' => false,
        'installments_enabled' => false,
    ]);

    expect($pack->fresh()->allowed_installments)->toBeNull();

    $this->assertDatabaseMissing('pack_product_items', [
        'pack_product_id' => $pack->id,
        'product_id' => $oldProduct->id,
    ]);
    $this->assertDatabaseHas('pack_product_items', [
        'pack_product_id' => $pack->id,
        'product_id' => $newProduct->id,
        'quantity' => 4,
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->delete("/mobile/packs/{$pack->id}")
        ->assertRedirect(route('mobile.packs.index', absolute: false));

    $this->assertDatabaseMissing('pack_products', [
        'id' => $pack->id,
    ]);
});

test('authenticated practitioners can assign and revoke a pack from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Pack',
        'email' => 'camille.pack.mobile@example.test',
    ]);

    $consultation = Product::create([
        'user_id' => $user->id,
        'name' => 'Consultation incluse',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
    ]);

    $massage = Product::create([
        'user_id' => $user->id,
        'name' => 'Massage inclus',
        'price' => 60,
        'tax_rate' => 0,
        'duration' => 45,
    ]);

    $pack = PackProduct::create([
        'user_id' => $user->id,
        'name' => 'Pack attribution mobile',
        'description' => 'Pack a attribuer',
        'price' => 240,
        'tax_rate' => 0,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    PackProductItem::create([
        'pack_product_id' => $pack->id,
        'product_id' => $consultation->id,
        'quantity' => 2,
        'sort_order' => 0,
    ]);
    PackProductItem::create([
        'pack_product_id' => $pack->id,
        'product_id' => $massage->id,
        'quantity' => 1,
        'sort_order' => 1,
    ]);

    $this->actingAs($user)
        ->get("/mobile/packs/{$pack->id}")
        ->assertOk()
        ->assertSee('Pack attribution mobile')
        ->assertSee('Consultation incluse')
        ->assertSee('Attribuer a un client');

    $this->actingAs($user)
        ->post("/mobile/packs/{$pack->id}/assign", [
            'client_profile_id' => $client->id,
            'purchased_at' => '2026-07-01',
            'expires_at' => '2026-12-31',
            'notes' => 'Attribue depuis mobile',
        ])
        ->assertRedirect(route('mobile.packs.show', $pack, absolute: false));

    $purchase = PackPurchase::where('pack_product_id', $pack->id)->firstOrFail();

    $this->assertDatabaseHas('pack_purchases', [
        'id' => $purchase->id,
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'status' => 'active',
        'notes' => 'Attribue depuis mobile',
    ]);

    $this->assertDatabaseHas('pack_purchase_items', [
        'pack_purchase_id' => $purchase->id,
        'product_id' => $consultation->id,
        'quantity_total' => 2,
        'quantity_remaining' => 2,
    ]);
    $this->assertDatabaseHas('pack_purchase_items', [
        'pack_purchase_id' => $purchase->id,
        'product_id' => $massage->id,
        'quantity_total' => 1,
        'quantity_remaining' => 1,
    ]);

    $this->actingAs($user)
        ->get("/mobile/packs/{$pack->id}")
        ->assertOk()
        ->assertSee('Camille Pack')
        ->assertSee('Attribue depuis mobile');

    $this->actingAs($user)
        ->delete("/mobile/pack-purchases/{$purchase->id}/revoke")
        ->assertRedirect(route('mobile.packs.show', $pack, absolute: false));

    expect($purchase->fresh()->status)->toBe('cancelled');
});

test('mobile pack actions are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $product = Product::create([
        'user_id' => $owner->id,
        'name' => 'Prestation pack privee',
        'price' => 70,
        'tax_rate' => 0,
        'duration' => 60,
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Client',
        'last_name' => 'Pack prive',
    ]);

    $pack = PackProduct::create([
        'user_id' => $owner->id,
        'name' => 'Pack prive',
        'price' => 140,
        'tax_rate' => 0,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    PackProductItem::create([
        'pack_product_id' => $pack->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'sort_order' => 0,
    ]);

    $purchase = PackPurchase::create([
        'user_id' => $owner->id,
        'pack_product_id' => $pack->id,
        'client_profile_id' => $client->id,
        'purchased_at' => now(),
        'status' => 'active',
    ]);

    PackPurchaseItem::create([
        'pack_purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity_total' => 2,
        'quantity_remaining' => 2,
    ]);

    $this->actingAs($other)
        ->get("/mobile/packs/{$pack->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->get("/mobile/packs/{$pack->id}/edit")
        ->assertForbidden();

    $this->actingAs($other)
        ->put("/mobile/packs/{$pack->id}", [
            'name' => 'Tentative interdite',
            'price' => 99,
            'tax_rate' => 0,
            'is_active' => 1,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 1,
            'installments_enabled' => 0,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post("/mobile/packs/{$pack->id}/assign", [
            'client_profile_id' => $client->id,
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/packs/{$pack->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->delete("/mobile/pack-purchases/{$purchase->id}/revoke")
        ->assertForbidden();
});

test('mobile pack form rejects products from another practitioner', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $otherProduct = Product::create([
        'user_id' => $other->id,
        'name' => 'Prestation hors compte',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
    ]);

    $this->actingAs($user)
        ->from('/mobile/packs/create')
        ->post('/mobile/packs', [
            'name' => 'Pack interdit',
            'price' => 160,
            'tax_rate' => 0,
            'is_active' => 1,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 1,
            'installments_enabled' => 0,
            'items' => [
                ['product_id' => $otherProduct->id, 'quantity' => 2],
            ],
        ])
        ->assertRedirect('/mobile/packs/create')
        ->assertSessionHasErrors('items');

    $this->assertDatabaseMissing('pack_products', [
        'user_id' => $user->id,
        'name' => 'Pack interdit',
    ]);
});

test('authenticated practitioners can create a gift voucher from mobile', function () {
    Queue::fake();

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/bons-cadeaux/create')
        ->assertOk()
        ->assertSee('Nouveau bon cadeau')
        ->assertSee('Acheteur');

    $response = $this->actingAs($user)
        ->post('/mobile/bons-cadeaux', [
            'amount_eur' => 120,
            'expires_at' => now()->addMonths(6)->toDateString(),
            'buyer_name' => 'Acheteur Mobile',
            'buyer_email' => 'buyer.mobile@example.test',
            'buyer_phone' => '0601020304',
            'recipient_name' => 'Beneficiaire Mobile',
            'recipient_email' => 'recipient.mobile@example.test',
            'message' => 'Profite bien de ce bon cadeau',
            'create_sale_invoice' => 1,
            'payment_method' => 'card',
        ]);

    $voucher = GiftVoucher::where('buyer_email', 'buyer.mobile@example.test')->firstOrFail();

    $response->assertRedirect(route('mobile.gift-vouchers.show', $voucher, absolute: false));

    $this->assertDatabaseHas('gift_vouchers', [
        'id' => $voucher->id,
        'user_id' => $user->id,
        'original_amount_cents' => 12000,
        'remaining_amount_cents' => 12000,
        'buyer_name' => 'Acheteur Mobile',
        'recipient_name' => 'Beneficiaire Mobile',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    expect($voucher->sale_invoice_id)->not->toBeNull();

    $this->assertDatabaseHas('invoices', [
        'id' => $voucher->sale_invoice_id,
        'user_id' => $user->id,
        'type' => 'invoice',
    ]);

    $this->assertDatabaseHas('receipts', [
        'invoice_id' => $voucher->sale_invoice_id,
        'source' => 'manual',
        'payment_method' => 'card',
    ]);

    Queue::assertPushed(SendGiftVoucherEmailsJob::class, function ($job) use ($voucher) {
        return $job->voucherId === $voucher->id;
    });
});

test('authenticated practitioners can view and redeem a gift voucher from mobile', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $voucher = GiftVoucher::create([
        'user_id' => $user->id,
        'code' => 'AM-MOBILE-REDEEM-0001',
        'original_amount_cents' => 15000,
        'remaining_amount_cents' => 15000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Acheteur Solde',
        'buyer_email' => 'buyer.solde@example.test',
        'recipient_name' => 'Camille Cadeau',
        'recipient_email' => 'camille.cadeau@example.test',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    $this->actingAs($user)
        ->get("/mobile/bons-cadeaux/{$voucher->id}")
        ->assertOk()
        ->assertSee('AM-MOBILE-REDEEM-0001')
        ->assertSee('Camille Cadeau')
        ->assertSee('Deduction');

    $this->actingAs($user)
        ->from("/mobile/bons-cadeaux/{$voucher->id}")
        ->post("/mobile/bons-cadeaux/{$voucher->id}/redeem", [
            'amount_eur' => 45.5,
            'note' => 'Seance mobile',
        ])
        ->assertRedirect(route('mobile.gift-vouchers.show', $voucher, absolute: false));

    $voucher->refresh();

    expect($voucher->remaining_amount_cents)->toBe(10450);
    expect($voucher->is_active)->toBeTrue();

    $this->assertDatabaseHas('gift_voucher_redemptions', [
        'gift_voucher_id' => $voucher->id,
        'user_id' => $user->id,
        'amount_cents' => 4550,
        'note' => 'Seance mobile',
        'source' => 'manual',
        'status' => 'applied',
    ]);
});

test('authenticated practitioners can resend and disable a gift voucher from mobile', function () {
    Queue::fake();

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $voucher = GiftVoucher::create([
        'user_id' => $user->id,
        'code' => 'AM-MOBILE-ACTION-0001',
        'original_amount_cents' => 8000,
        'remaining_amount_cents' => 8000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Acheteur Action',
        'buyer_email' => 'buyer.action@example.test',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    $this->actingAs($user)
        ->post("/mobile/bons-cadeaux/{$voucher->id}/resend")
        ->assertRedirect(route('mobile.gift-vouchers.show', $voucher, absolute: false));

    Queue::assertPushed(SendGiftVoucherEmailsJob::class, function ($job) use ($voucher) {
        return $job->voucherId === $voucher->id;
    });

    $this->actingAs($user)
        ->post("/mobile/bons-cadeaux/{$voucher->id}/disable")
        ->assertRedirect(route('mobile.gift-vouchers.show', $voucher, absolute: false));

    expect($voucher->fresh()->is_active)->toBeFalse();
});

test('mobile gift voucher actions are protected by ownership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $voucher = GiftVoucher::create([
        'user_id' => $owner->id,
        'code' => 'AM-MOBILE-PRIVATE-0001',
        'original_amount_cents' => 7000,
        'remaining_amount_cents' => 7000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_email' => 'private.voucher@example.test',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    $this->actingAs($other)
        ->get("/mobile/bons-cadeaux/{$voucher->id}")
        ->assertForbidden();

    $this->actingAs($other)
        ->post("/mobile/bons-cadeaux/{$voucher->id}/redeem", [
            'amount_eur' => 10,
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post("/mobile/bons-cadeaux/{$voucher->id}/disable")
        ->assertForbidden();

    expect($voucher->fresh()->remaining_amount_cents)->toBe(7000);
    expect($voucher->fresh()->is_active)->toBeTrue();
});

test('mobile gift voucher feature gate matches the web feature gate', function () {
    $starter = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_starter_mensuelle',
    ]);

    $this->actingAs($starter)
        ->get('/mobile/bons-cadeaux')
        ->assertOk()
        ->assertSee('Fonction verrouillee');

    $this->actingAs($starter)
        ->get('/mobile/bons-cadeaux/create')
        ->assertForbidden();
});

test('mobile gift voucher redemption cannot exceed remaining balance', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $voucher = GiftVoucher::create([
        'user_id' => $user->id,
        'code' => 'AM-MOBILE-LIMIT-0001',
        'original_amount_cents' => 3000,
        'remaining_amount_cents' => 3000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_email' => 'limit.voucher@example.test',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);

    $this->actingAs($user)
        ->from("/mobile/bons-cadeaux/{$voucher->id}")
        ->post("/mobile/bons-cadeaux/{$voucher->id}/redeem", [
            'amount_eur' => 40,
        ])
        ->assertRedirect("/mobile/bons-cadeaux/{$voucher->id}")
        ->assertSessionHasErrors('amount_eur');

    expect($voucher->fresh()->remaining_amount_cents)->toBe(3000);
});

test('mobile received invoices show a disabled sandbox state for ordinary practitioners', function () {
    Config::set('services.super_pdp.environment', 'sandbox');
    Config::set('services.super_pdp.allowed_emails', []);

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get('/mobile/factures-recues')
        ->assertOk()
        ->assertSee('Factures recues')
        ->assertSee('SUPER PDP sandbox non active');

    $this->assertDatabaseMissing('super_pdp_connections', [
        'user_id' => $user->id,
    ]);
});

test('enabled practitioners can view received invoices from mobile', function () {
    Config::set('services.super_pdp.environment', 'sandbox');

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    Config::set('services.super_pdp.allowed_emails', [strtolower($user->email)]);

    $connection = SuperPdpConnection::create([
        'user_id' => $user->id,
        'environment' => 'sandbox',
        'status' => SuperPdpConnection::STATUS_CONNECTED,
        'receiving_invoices_enabled' => true,
        'refresh_token' => 'refresh-token-mobile',
        'super_pdp_company_name' => 'Cabinet Mobile',
        'last_synced_at' => '2026-07-01 09:00:00',
    ]);

    $invoice = SuperPdpReceivedInvoice::create([
        'connection_id' => $connection->id,
        'user_id' => $user->id,
        'super_pdp_invoice_id' => 777,
        'super_pdp_company_id' => 123,
        'direction' => 'in',
        'external_id' => 'EXT-MOBILE-001',
        'invoice_number' => 'INV-2026-001',
        'invoice_date' => '2026-06-26',
        'seller_name' => 'Tricatel',
        'buyer_name' => 'Burger Queen',
        'currency_code' => 'EUR',
        'total_with_vat' => 120.50,
        'latest_event_code' => 'fr:200',
        'latest_event_text' => 'Deposee',
        'latest_event_at' => '2026-06-26 08:05:00',
        'last_synced_at' => '2026-07-01 09:00:00',
    ]);

    $this->actingAs($user)
        ->get('/mobile/factures-recues')
        ->assertOk()
        ->assertSee('INV-2026-001')
        ->assertSee('Tricatel')
        ->assertSee('120,50 EUR')
        ->assertSee('Connectee');

    $this->actingAs($user)
        ->get("/mobile/factures-recues/{$invoice->id}")
        ->assertOk()
        ->assertSee('INV-2026-001')
        ->assertSee('Burger Queen')
        ->assertSee('Factur-X PDF')
        ->assertSee('EXT-MOBILE-001');
});

test('enabled practitioners can trigger received invoice sync from mobile', function () {
    Config::set('services.super_pdp.environment', 'sandbox');

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    Config::set('services.super_pdp.allowed_emails', [strtolower($user->email)]);

    $connection = SuperPdpConnection::create([
        'user_id' => $user->id,
        'environment' => 'sandbox',
        'status' => SuperPdpConnection::STATUS_CONNECTED,
        'receiving_invoices_enabled' => true,
        'refresh_token' => 'refresh-token-sync',
    ]);

    $this->mock(SuperPdpReceivedInvoiceSyncService::class, function ($mock) use ($connection) {
        $mock->shouldReceive('sync')
            ->once()
            ->withArgs(fn (SuperPdpConnection $givenConnection) => $givenConnection->id === $connection->id)
            ->andReturn(2);
    });

    $this->actingAs($user)
        ->get(route('mobile.received-invoices.index', ['sync' => 1]))
        ->assertRedirect(route('mobile.received-invoices.index', absolute: false))
        ->assertSessionHas('success', '2 facture(s) recue(s) synchronisee(s).');
});

test('mobile received invoice detail and download are protected by ownership', function () {
    Config::set('services.super_pdp.environment', 'sandbox');

    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    Config::set('services.super_pdp.allowed_emails', [
        strtolower($owner->email),
        strtolower($other->email),
    ]);

    $connection = SuperPdpConnection::create([
        'user_id' => $owner->id,
        'environment' => 'sandbox',
        'status' => SuperPdpConnection::STATUS_CONNECTED,
        'receiving_invoices_enabled' => true,
        'refresh_token' => 'refresh-token-private',
    ]);

    $invoice = SuperPdpReceivedInvoice::create([
        'connection_id' => $connection->id,
        'user_id' => $owner->id,
        'super_pdp_invoice_id' => 888,
        'direction' => 'in',
        'invoice_number' => 'INV-PRIVATE',
        'seller_name' => 'Fournisseur prive',
        'currency_code' => 'EUR',
        'total_with_vat' => 99.90,
    ]);

    $this->actingAs($other)
        ->get("/mobile/factures-recues/{$invoice->id}")
        ->assertNotFound();

    $this->actingAs($other)
        ->get("/mobile/factures-recues/{$invoice->id}/download")
        ->assertNotFound();
});

test('mobile received invoice download proxies the selected document format', function () {
    Config::set('services.super_pdp.environment', 'sandbox');

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    Config::set('services.super_pdp.allowed_emails', [strtolower($user->email)]);

    $connection = SuperPdpConnection::create([
        'user_id' => $user->id,
        'environment' => 'sandbox',
        'status' => SuperPdpConnection::STATUS_CONNECTED,
        'receiving_invoices_enabled' => true,
        'refresh_token' => 'refresh-token-download',
    ]);

    $invoice = SuperPdpReceivedInvoice::create([
        'connection_id' => $connection->id,
        'user_id' => $user->id,
        'super_pdp_invoice_id' => 999,
        'direction' => 'in',
        'invoice_number' => 'INV-DOWNLOAD',
        'seller_name' => 'Download Supplier',
        'currency_code' => 'EUR',
        'total_with_vat' => 42,
    ]);

    $fakeResponse = new \Illuminate\Http\Client\Response(
        new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/xml'], '<Invoice>mobile</Invoice>')
    );

    $this->mock(SuperPdpApiClient::class, function ($mock) use ($connection, $fakeResponse) {
        $mock->shouldReceive('invoiceDocument')
            ->once()
            ->withArgs(fn (SuperPdpConnection $givenConnection, int $invoiceId, string $format) => (
                $givenConnection->id === $connection->id
                && $invoiceId === 999
                && $format === 'ubl'
            ))
            ->andReturn($fakeResponse);
    });

    $this->actingAs($user)
        ->get(route('mobile.received-invoices.download', [$invoice, 'format' => 'ubl']))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<Invoice>mobile</Invoice>', false);
});

test('mobile digital trainings can be created updated viewed and deleted', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Programme accompagnement',
        'description' => 'Base produit',
        'price' => 80,
        'tax_rate' => 0,
    ]);

    $this->actingAs($user)
        ->get(route('mobile.digital-trainings.create', absolute: false))
        ->assertOk()
        ->assertSee('Nouvelle formation')
        ->assertSee('Programme accompagnement');

    $response = $this->actingAs($user)
        ->post(route('mobile.digital-trainings.store', absolute: false), [
            'title' => 'Gestion du stress mobile',
            'description' => 'Programme court pour les clients.',
            'tags' => 'stress, sommeil',
            'is_free' => '0',
            'price_eur' => '49,90',
            'tax_rate' => '20',
            'installments_enabled' => '1',
            'allowed_installments' => [2, 4],
            'access_type' => 'public',
            'status' => 'published',
            'estimated_duration_minutes' => '90',
            'product_id' => $product->id,
            'use_global_retractation_notice' => '0',
        ]);

    $training = DigitalTraining::where('user_id', $user->id)->firstOrFail();

    $response->assertRedirect(route('mobile.digital-trainings.show', $training, false));

    expect($training->title)->toBe('Gestion du stress mobile')
        ->and($training->price_cents)->toBe(4990)
        ->and($training->tags)->toBe(['stress', 'sommeil'])
        ->and($training->allowed_installments)->toBe([2, 4])
        ->and($training->product_id)->toBe($product->id);

    $module = TrainingModule::create([
        'digital_training_id' => $training->id,
        'title' => 'Module 1',
        'description' => 'Introduction',
        'display_order' => 1,
    ]);

    TrainingBlock::create([
        'training_module_id' => $module->id,
        'type' => 'text',
        'title' => 'Bienvenue',
        'content' => 'Contenu',
        'display_order' => 1,
    ]);

    DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Claire Martin',
        'participant_email' => 'claire@example.test',
        'access_token' => 'mobile-training-token',
        'progress_percent' => 25,
        'source' => DigitalTrainingEnrollment::SOURCE_MANUAL,
    ]);

    $this->actingAs($user)
        ->get(route('mobile.digital-trainings.index', absolute: false))
        ->assertOk()
        ->assertSee('Formations digitales')
        ->assertSee('Gestion du stress mobile')
        ->assertSee('49,90 EUR');

    $this->actingAs($user)
        ->get(route('mobile.digital-trainings.show', $training, false))
        ->assertOk()
        ->assertSee('Plan de formation')
        ->assertSee('Module 1')
        ->assertSee('Claire Martin')
        ->assertSee('Copier le lien');

    $this->actingAs($user)
        ->get(route('mobile.digital-trainings.edit', $training, false))
        ->assertOk()
        ->assertSee('Modifier la formation');

    $this->actingAs($user)
        ->put(route('mobile.digital-trainings.update', $training, false), [
            'title' => 'Gestion du stress mobile ajustee',
            'description' => 'Version gratuite.',
            'tags' => 'respiration',
            'is_free' => '1',
            'free_access_requires_identity' => '1',
            'free_access_is_open' => '0',
            'price_eur' => '',
            'tax_rate' => '0',
            'installments_enabled' => '0',
            'access_type' => 'private',
            'status' => 'draft',
            'estimated_duration_minutes' => '45',
            'product_id' => '',
            'use_global_retractation_notice' => '0',
        ])
        ->assertRedirect(route('mobile.digital-trainings.show', $training, false));

    $training->refresh();

    expect($training->title)->toBe('Gestion du stress mobile ajustee')
        ->and($training->is_free)->toBeTrue()
        ->and($training->price_cents)->toBeNull()
        ->and($training->free_access_requires_identity)->toBeTrue()
        ->and($training->free_access_is_open)->toBeFalse()
        ->and($training->installments_enabled)->toBeFalse()
        ->and($training->allowed_installments)->toBeNull()
        ->and($training->access_type)->toBe('private')
        ->and($training->status)->toBe('draft')
        ->and($training->estimated_duration_minutes)->toBe(45);

    $this->actingAs($user)
        ->delete(route('mobile.digital-trainings.destroy', $training, false))
        ->assertRedirect(route('mobile.digital-trainings.index', absolute: false));

    $this->assertDatabaseMissing('digital_trainings', [
        'id' => $training->id,
    ]);
});

test('mobile digital trainings are protected by owner and active license', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $inactive = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'inactive',
    ]);

    $training = DigitalTraining::create([
        'user_id' => $owner->id,
        'title' => 'Formation privee',
        'slug' => 'formation-privee',
        'is_free' => true,
        'access_type' => 'public',
        'status' => 'draft',
    ]);

    $this->actingAs($inactive)
        ->get(route('mobile.digital-trainings.index', absolute: false))
        ->assertRedirect('/license-tiers/pricing');

    $this->actingAs($other)
        ->get(route('mobile.digital-trainings.show', $training, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->get(route('mobile.digital-trainings.edit', $training, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->put(route('mobile.digital-trainings.update', $training, false), [
            'title' => 'Tentative',
            'is_free' => '1',
            'access_type' => 'public',
            'status' => 'draft',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('mobile.digital-trainings.destroy', $training, false))
        ->assertForbidden();
});

test('mobile communities can be created updated viewed and deleted', function () {
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('mobile.communities.create', absolute: false))
        ->assertOk()
        ->assertSee('Nouvelle communaute');

    $response = $this->actingAs($user)
        ->post(route('mobile.communities.store', absolute: false), [
            'name' => 'Groupe sommeil mobile',
            'description' => 'Suivi prive du programme.',
        ]);

    $community = CommunityGroup::where('user_id', $user->id)->firstOrFail();

    $response->assertRedirect(route('mobile.communities.show', $community, false));

    expect($community->name)->toBe('Groupe sommeil mobile')
        ->and($community->channels()->pluck('name')->all())->toBe(['General', 'Annonces']);

    $this->actingAs($user)
        ->get(route('mobile.communities.index', absolute: false))
        ->assertOk()
        ->assertSee('Communautes')
        ->assertSee('Groupe sommeil mobile');

    $this->actingAs($user)
        ->get(route('mobile.communities.show', $community, false))
        ->assertOk()
        ->assertSee('Messages')
        ->assertSee('Membres')
        ->assertSee('Groupe sommeil mobile');

    $this->actingAs($user)
        ->put(route('mobile.communities.update', $community, false), [
            'name' => 'Groupe sommeil mobile ajuste',
            'description' => 'Version archivee.',
            'is_archived' => '1',
        ])
        ->assertRedirect(route('mobile.communities.show', $community, false));

    $community->refresh();

    expect($community->name)->toBe('Groupe sommeil mobile ajuste')
        ->and($community->is_archived)->toBeTrue();

    $this->actingAs($user)
        ->get(route('mobile.communities.show', $community, false))
        ->assertOk()
        ->assertSee('Archivee')
        ->assertSee('Version archivee');

    $this->actingAs($user)
        ->delete(route('mobile.communities.destroy', $community, false))
        ->assertRedirect(route('mobile.communities.index', absolute: false));

    $this->assertDatabaseMissing('community_groups', [
        'id' => $community->id,
    ]);
});

test('mobile communities can invite members remove members and send messages', function () {
    Mail::fake();
    Storage::fake('public');

    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Claire',
        'last_name' => 'Martin',
        'phone' => '0600000000',
    ]);

    $community = CommunityGroup::create([
        'user_id' => $user->id,
        'name' => 'Cercle respiration',
        'description' => 'Messages du groupe.',
    ]);

    $channel = $community->channels()->create([
        'name' => 'General',
        'channel_type' => CommunityChannel::TYPE_DISCUSSION,
        'position' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('mobile.communities.show', $community, false))
        ->assertOk()
        ->assertSee('Cercle respiration')
        ->assertSee('Claire Martin');

    $this->actingAs($user)
        ->post(route('mobile.communities.members.store', $community, false), [
            'client_profile_id' => $client->id,
        ])
        ->assertRedirect(route('mobile.communities.show', $community, false));

    $member = CommunityMember::where('community_group_id', $community->id)->firstOrFail();

    expect($member->client_profile_id)->toBe($client->id)
        ->and($member->status)->toBe(CommunityMember::STATUS_INVITED)
        ->and($member->invitation_email_sent_at)->toBeNull();

    Mail::assertNothingQueued();

    $this->actingAs($user)
        ->post(route('mobile.communities.messages.store', $community, false), [
            'community_channel_id' => $channel->id,
            'content' => 'Bienvenue dans le cercle mobile.',
        ])
        ->assertRedirect(route('mobile.communities.show', ['community' => $community->id, 'channel' => $channel->id], false));

    $message = CommunityMessage::where('community_group_id', $community->id)->firstOrFail();

    expect($message->sender_type)->toBe(CommunityMessage::SENDER_PRACTITIONER)
        ->and($message->content)->toBe('Bienvenue dans le cercle mobile.');

    $this->actingAs($user)
        ->get(route('mobile.communities.show', ['community' => $community->id, 'channel' => $channel->id], false))
        ->assertOk()
        ->assertSee('Bienvenue dans le cercle mobile.')
        ->assertSee('Claire Martin');

    $this->actingAs($user)
        ->delete(route('mobile.communities.members.destroy', [$community, $member], false))
        ->assertRedirect(route('mobile.communities.show', $community, false));

    expect($member->fresh()->status)->toBe(CommunityMember::STATUS_REMOVED);
});

test('mobile community actions are protected by owner and active license', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $other = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $inactive = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'inactive',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Nina',
        'last_name' => 'Bernard',
    ]);

    $community = CommunityGroup::create([
        'user_id' => $owner->id,
        'name' => 'Communaute privee',
    ]);

    $channel = $community->channels()->create([
        'name' => 'General',
        'channel_type' => CommunityChannel::TYPE_DISCUSSION,
        'position' => 1,
    ]);

    $member = CommunityMember::create([
        'community_group_id' => $community->id,
        'client_profile_id' => $client->id,
        'status' => CommunityMember::STATUS_INVITED,
        'invited_at' => now(),
    ]);

    $this->actingAs($inactive)
        ->get(route('mobile.communities.index', absolute: false))
        ->assertRedirect('/license-tiers/pricing');

    $this->actingAs($other)
        ->get(route('mobile.communities.show', $community, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->get(route('mobile.communities.edit', $community, false))
        ->assertForbidden();

    $this->actingAs($other)
        ->put(route('mobile.communities.update', $community, false), [
            'name' => 'Tentative',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post(route('mobile.communities.members.store', $community, false), [
            'client_profile_id' => $client->id,
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->post(route('mobile.communities.messages.store', $community, false), [
            'community_channel_id' => $channel->id,
            'content' => 'Intrusion',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('mobile.communities.members.destroy', [$community, $member], false))
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('mobile.communities.destroy', $community, false))
        ->assertForbidden();
});

test('mobile google reviews dashboard shows disconnected state and restricts non therapists', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($therapist)
        ->get(route('mobile.google-reviews.index', absolute: false))
        ->assertOk()
        ->assertSee('Avis Google')
        ->assertSee('Connecter Google')
        ->assertSee('Aucun avis importe');

    $nonTherapist = User::factory()->create([
        'is_therapist' => false,
        'license_status' => 'active',
    ]);

    $this->actingAs($nonTherapist)
        ->get(route('mobile.google-reviews.index', absolute: false))
        ->assertForbidden();
});

test('mobile google reviews show account reviews and keep actions on mobile routes', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $account = GoogleBusinessAccount::create([
        'user_id' => $therapist->id,
        'account_id' => 'acc_123',
        'account_display_name' => 'Compte Test',
        'location_id' => 'loc_1',
        'location_title' => 'Cabinet Principal',
        'access_token' => 'token_ok',
        'access_token_expires_at' => now()->addHour(),
        'last_synced_at' => now()->subDay(),
    ]);

    Testimonial::create([
        'therapist_id' => $therapist->id,
        'source' => 'google',
        'external_review_id' => 'review_1',
        'rating' => 5,
        'reviewer_name' => 'Lucie Martin',
        'testimonial' => 'Excellent accompagnement.',
        'visible_on_public_profile' => true,
        'external_created_at' => now()->subDays(3),
        'owner_reply' => 'Merci pour votre confiance.',
    ]);

    Http::fake([
        'https://mybusinessaccountmanagement.googleapis.com/*' => Http::response([
            'accounts' => [
                ['name' => 'accounts/acc_123', 'accountName' => 'Cabinet Paris'],
                ['name' => 'accounts/acc_456', 'accountName' => 'Cabinet Lyon'],
            ],
        ], 200),
        'https://mybusinessbusinessinformation.googleapis.com/v1/accounts/acc_123/locations*' => Http::response([
            'locations' => [
                ['name' => 'locations/loc_1', 'title' => 'Cabinet Principal'],
            ],
        ], 200),
        'https://mybusinessbusinessinformation.googleapis.com/v1/accounts/acc_456/locations*' => Http::response([
            'locations' => [
                ['name' => 'locations/loc_9', 'title' => 'Cabinet Bellecour'],
            ],
        ], 200),
    ]);

    $this->actingAs($therapist)
        ->get(route('mobile.google-reviews.index', absolute: false))
        ->assertOk()
        ->assertSee('Cabinet Principal')
        ->assertSee('Cabinet Principal (Cabinet Paris)')
        ->assertSee('Cabinet Bellecour (Cabinet Lyon)')
        ->assertSee('Lucie Martin')
        ->assertSee('Excellent accompagnement.')
        ->assertSee('5/5');

    $this->actingAs($therapist)
        ->post(route('mobile.google-reviews.sync', absolute: false), [
            'location_selection' => 'acc_123|loc_unknown',
        ])
        ->assertRedirect(route('mobile.google-reviews.index', absolute: false))
        ->assertSessionHas('error');

    $account->refresh();
    expect($account->location_id)->toBe('loc_1');

    $this->actingAs($therapist)
        ->post(route('mobile.google-reviews.disconnect', absolute: false))
        ->assertRedirect(route('mobile.google-reviews.index', absolute: false));

    $this->assertDatabaseMissing('google_business_accounts', [
        'id' => $account->id,
    ]);
});

test('mobile client login uses the client guard and protected pages redirect to client login', function () {
    $practitioner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $practitioner->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice-client@example.test',
        'password' => 'password',
    ]);

    $this->get(route('mobile.client.home', absolute: false))
        ->assertRedirect(route('mobile.client.login', absolute: false));

    $this->get(route('mobile.client.login', absolute: false))
        ->assertOk()
        ->assertSee('Espace client')
        ->assertSee('Connexion');

    $this->post(route('mobile.client.login.store', absolute: false), [
        'email' => $client->email,
        'password' => 'password',
    ])->assertRedirect(route('mobile.client.home', absolute: false));

    $this->assertAuthenticatedAs($client, 'client');
});

test('authenticated clients can use the mobile portal messages and documents', function () {
    Mail::fake();
    Storage::fake('public');

    $practitioner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'email' => 'praticien-mobile@example.test',
    ]);

    $client = ClientProfile::create([
        'user_id' => $practitioner->id,
        'first_name' => 'Nina',
        'last_name' => 'Durand',
        'email' => 'nina-mobile@example.test',
        'password' => 'password',
    ]);

    Message::create([
        'client_profile_id' => $client->id,
        'user_id' => $practitioner->id,
        'sender_type' => 'therapist',
        'content' => 'Bonjour Nina, voici votre suivi.',
    ]);

    $this->actingAs($client, 'client')
        ->get(route('mobile.client.home', absolute: false))
        ->assertOk()
        ->assertSee('Bonjour Nina')
        ->assertSee('Bonjour Nina, voici votre suivi.')
        ->assertSee('Documents');

    $this->actingAs($client, 'client')
        ->post(route('mobile.client.messages.store', absolute: false), [
            'content' => 'Merci pour le retour.',
        ])
        ->assertRedirect(route('mobile.client.messages.index', absolute: false));

    $this->assertDatabaseHas('messages', [
        'client_profile_id' => $client->id,
        'sender_type' => 'client',
        'content' => 'Merci pour le retour.',
    ]);

    Mail::assertQueued(\App\Mail\ClientMessageReceivedTherapistMail::class);

    $this->actingAs($client, 'client')
        ->post(route('mobile.client.files.store', absolute: false), [
            'document' => UploadedFile::fake()->create('bilan.pdf', 12, 'application/pdf'),
        ])
        ->assertRedirect(route('mobile.client.home', absolute: false));

    $file = ClientFile::firstOrFail();
    Storage::disk('public')->assertExists($file->file_path);
    Mail::assertQueued(\App\Mail\ClientFileUploadedTherapistMail::class);

    $this->actingAs($client, 'client')
        ->get(route('mobile.client.files.download', $file, false))
        ->assertOk();
});

test('authenticated clients can accept and use mobile communities', function () {
    Storage::fake('public');

    $practitioner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'company_name' => 'Cabinet Mobile',
    ]);

    $client = ClientProfile::create([
        'user_id' => $practitioner->id,
        'first_name' => 'Claire',
        'last_name' => 'Martin',
        'email' => 'claire-mobile@example.test',
        'password' => 'password',
    ]);

    $community = CommunityGroup::create([
        'user_id' => $practitioner->id,
        'name' => 'Groupe respiration',
        'description' => 'Echanges du programme mobile.',
    ]);

    $discussion = $community->channels()->create([
        'name' => 'General',
        'channel_type' => CommunityChannel::TYPE_DISCUSSION,
        'position' => 1,
    ]);

    $announcements = $community->channels()->create([
        'name' => 'Annonces',
        'channel_type' => CommunityChannel::TYPE_ANNOUNCEMENTS,
        'position' => 2,
    ]);

    CommunityMember::create([
        'community_group_id' => $community->id,
        'client_profile_id' => $client->id,
        'status' => CommunityMember::STATUS_INVITED,
        'invited_at' => now(),
    ]);

    $this->actingAs($client, 'client')
        ->get(route('mobile.client.communities.index', absolute: false))
        ->assertOk()
        ->assertSee('Groupe respiration')
        ->assertSee('Invitations');

    $this->actingAs($client, 'client')
        ->post(route('mobile.client.communities.accept', $community, false))
        ->assertRedirect(route('mobile.client.communities.show', $community, false));

    expect(CommunityMember::first()->fresh()->status)->toBe(CommunityMember::STATUS_ACTIVE);

    $this->actingAs($client, 'client')
        ->get(route('mobile.client.communities.show', ['community' => $community->id, 'channel' => $discussion->id], false))
        ->assertOk()
        ->assertSee('Groupe respiration')
        ->assertSee('General')
        ->assertSee('Echanges du programme mobile.');

    $this->actingAs($client, 'client')
        ->post(route('mobile.client.communities.messages.store', $community, false), [
            'community_channel_id' => $discussion->id,
            'content' => 'Merci pour ce groupe.',
            'attachments' => [
                UploadedFile::fake()->create('note.txt', 4, 'text/plain'),
            ],
        ])
        ->assertRedirect(route('mobile.client.communities.show', ['community' => $community->id, 'channel' => $discussion->id], false));

    $message = CommunityMessage::firstOrFail();

    expect($message->sender_type)->toBe(CommunityMessage::SENDER_CLIENT)
        ->and($message->attachments()->count())->toBe(1);

    $this->actingAs($client, 'client')
        ->from(route('mobile.client.communities.show', ['community' => $community->id, 'channel' => $announcements->id], false))
        ->post(route('mobile.client.communities.messages.store', $community, false), [
            'community_channel_id' => $announcements->id,
            'content' => 'Je ne dois pas publier ici.',
        ])
        ->assertRedirect(route('mobile.client.communities.show', ['community' => $community->id, 'channel' => $announcements->id], false));

    expect(CommunityMessage::count())->toBe(1);
});

test('mobile client community pages are protected by membership', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $otherPractitioner = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Alice',
        'last_name' => 'Mobile',
        'email' => 'alice-mobile@example.test',
        'password' => 'password',
    ]);
    $otherClient = ClientProfile::create([
        'user_id' => $otherPractitioner->id,
        'first_name' => 'Other',
        'last_name' => 'Client',
        'email' => 'other-mobile@example.test',
        'password' => 'password',
    ]);

    $community = CommunityGroup::create([
        'user_id' => $owner->id,
        'name' => 'Groupe protege',
    ]);
    $channel = $community->channels()->create([
        'name' => 'General',
        'channel_type' => CommunityChannel::TYPE_DISCUSSION,
        'position' => 1,
    ]);

    CommunityMember::create([
        'community_group_id' => $community->id,
        'client_profile_id' => $client->id,
        'status' => CommunityMember::STATUS_ACTIVE,
        'invited_at' => now(),
        'joined_at' => now(),
    ]);

    $this->actingAs($otherClient, 'client')
        ->get(route('mobile.client.communities.show', $community, false))
        ->assertNotFound();

    $this->actingAs($otherClient, 'client')
        ->post(route('mobile.client.communities.messages.store', $community, false), [
            'community_channel_id' => $channel->id,
            'content' => 'Intrusion',
        ])
        ->assertNotFound();
});
