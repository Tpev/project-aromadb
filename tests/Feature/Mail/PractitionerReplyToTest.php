<?php

use App\Mail\AdminNewUserNotification;
use App\Mail\AppointmentCancelledByClient;
use App\Mail\AppointmentCreatedPatientMail;
use App\Mail\AppointmentCreatedTherapistMail;
use App\Mail\AppointmentEditedClientMail;
use App\Mail\AppointmentReminderClientMail;
use App\Mail\ClientFileUploadedTherapistMail;
use App\Mail\ClientMessageReceivedTherapistMail;
use App\Mail\Concerns\RepliesToPractitioner;
use App\Mail\DocumentSignRequestMail;
use App\Mail\EventReminderClientMail;
use App\Mail\InformationRequestMail;
use App\Mail\InvoiceMail;
use App\Mail\InvoicePaymentLinkMail;
use App\Mail\InvoicePaymentReminderMail;
use App\Mail\NewReservationNotification;
use App\Mail\NewsletterMail;
use App\Mail\OfferJourneyMessageMail;
use App\Mail\QuestionnaireCompletedMail;
use App\Mail\QuestionnaireSentMail;
use App\Mail\QuoteMail;
use App\Mail\ReservationConfirmation;
use App\Mail\WelcomeProMail;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Document;
use App\Models\DocumentSigning;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Newsletter;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Support\PractitionerReplyToResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\MailManager;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

uses(RefreshDatabase::class);

class PractitionerReplyToProbeMail extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public function __construct(public User $practitioner) {}

    public function build(): self
    {
        return $this->applyPractitionerReplyTo($this->practitioner)
            ->subject('Message de contrôle')
            ->html('<p>Contrôle Reply-To</p>');
    }
}

beforeEach(function (): void {
    config()->set([
        'mail.from.address' => 'contact@olithea.fr',
        'mail.from.name' => 'Olithea',
        'mail.practitioner_reply_to.enabled' => true,
        'mail.mailers.array' => ['transport' => 'array'],
    ]);

    app(MailManager::class)->forgetMailers();
});

function practitionerReplyToUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'name' => 'Camille Martin',
        'company_name' => 'Cabinet Harmonie',
        'company_email' => 'cabinet@example.test',
        'email' => 'camille@example.test',
    ], $attributes));
}

function practitionerReplyToSend(Mailable $mailable, string $recipient = 'client@example.test'): Email
{
    $mailer = app(MailManager::class)->mailer('array');
    $transport = $mailer->getSymfonyTransport();
    $transport->flush();

    $mailer->to($recipient)->send($mailable);

    return $transport->messages()->last()->getOriginalMessage();
}

function practitionerReplyToCount(Mailable $mailable): int
{
    if (method_exists($mailable, 'envelope')) {
        return count($mailable->envelope()->replyTo);
    }

    return count($mailable->replyTo);
}

function practitionerReplyToContext(): array
{
    $practitioner = practitionerReplyToUser();
    $client = ClientProfile::create([
        'user_id' => $practitioner->id,
        'first_name' => 'Nadine',
        'last_name' => 'Cliente',
        'email' => 'nadine@example.test',
    ]);
    $product = Product::create([
        'user_id' => $practitioner->id,
        'name' => 'Séance individuelle',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
        'dans_le_cabinet' => true,
        'visio' => false,
    ]);
    $appointment = Appointment::create([
        'user_id' => $practitioner->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->addDay(),
        'status' => 'confirmed',
        'duration' => 60,
        'type' => 'cabinet',
    ]);
    $event = Event::create([
        'user_id' => $practitioner->id,
        'name' => 'Atelier découverte',
        'description' => 'Atelier test',
        'start_date_time' => now()->addDays(2),
        'duration' => 90,
        'booking_required' => true,
        'limited_spot' => false,
        'showOnPortail' => true,
        'location' => 'Cabinet',
        'event_type' => 'in_person',
    ]);
    $reservation = Reservation::create([
        'event_id' => $event->id,
        'full_name' => 'Nadine Cliente',
        'email' => $client->email,
        'status' => 'confirmed',
    ]);
    $invoice = Invoice::create([
        'user_id' => $practitioner->id,
        'client_profile_id' => $client->id,
        'appointment_id' => $appointment->id,
        'invoice_date' => now()->toDateString(),
        'invoice_number' => 'F-REPLY-001',
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => 80,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 80,
    ]);
    $quote = Invoice::create([
        'user_id' => $practitioner->id,
        'client_profile_id' => $client->id,
        'invoice_date' => now()->toDateString(),
        'quote_number' => 'D-REPLY-001',
        'status' => 'Brouillon',
        'type' => 'quote',
        'total_amount' => 80,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 80,
    ]);
    $document = Document::create([
        'owner_user_id' => $practitioner->id,
        'client_profile_id' => $client->id,
        'original_name' => 'consentement.pdf',
        'storage_path' => 'documents/consentement.pdf',
        'status' => 'draft',
    ]);
    $signing = DocumentSigning::create([
        'document_id' => $document->id,
        'token' => 'reply-to-signature-token',
        'status' => 'pending',
        'expires_at' => now()->addDay(),
    ]);
    $newsletter = Newsletter::create([
        'user_id' => $practitioner->id,
        'title' => 'Actualités du cabinet',
        'subject' => 'Des nouvelles du cabinet',
        'from_name' => 'Cabinet Harmonie',
        'from_email' => 'ancienne-adresse@example.test',
        'background_color' => '#ffffff',
        'content_json' => json_encode([]),
        'status' => 'draft',
        'recipients_count' => 0,
    ]);

    return compact(
        'practitioner', 'client', 'product', 'appointment', 'event', 'reservation',
        'invoice', 'quote', 'document', 'signing', 'newsletter'
    );
}

test('real mime headers retain the Olithea sender and contain one practitioner reply-to', function () {
    $practitioner = practitionerReplyToUser([
        'company_name' => "Cabinet\nHarmonie",
        'company_email' => '  contact-cabinet@example.test  ',
    ]);

    $message = practitionerReplyToSend(new PractitionerReplyToProbeMail($practitioner));

    expect($message->getFrom())->toHaveCount(1)
        ->and($message->getFrom()[0]->getAddress())->toBe('contact@olithea.fr')
        ->and($message->getFrom()[0]->getName())->toBe('Olithea')
        ->and($message->getReplyTo())->toHaveCount(1)
        ->and($message->getReplyTo()[0]->getAddress())->toBe('contact-cabinet@example.test')
        ->and($message->getReplyTo()[0]->getName())->toBe('Cabinet Harmonie')
        ->and($message->getTo()[0]->getAddress())->toBe('client@example.test')
        ->and($message->getSubject())->toBe('Message de contrôle');
});

test('resolver applies safe address and name fallbacks without blocking delivery', function () {
    $accountFallback = practitionerReplyToUser([
        'company_name' => null,
        'company_email' => 'adresse-invalide',
        'email' => 'compte@example.test',
    ]);
    $accountFallback->setAttribute('business_name', 'Activité Sérénité');
    $platformFallback = practitionerReplyToUser([
        'company_name' => null,
        'name' => '',
        'company_email' => 'invalide',
        'email' => 'toujours-invalide',
    ]);

    $accountAddress = app(PractitionerReplyToResolver::class)->resolve($accountFallback);
    $platformAddress = app(PractitionerReplyToResolver::class)->resolve($platformFallback);

    expect($accountAddress?->address)->toBe('compte@example.test')
        ->and($accountAddress?->name)->toBe('Activité Sérénité')
        ->and($platformAddress?->address)->toBe('contact@olithea.fr')
        ->and($platformAddress?->name)->toBe('Olithea');
});

test('kill switch removes only the explicit reply-to header', function () {
    config()->set('mail.practitioner_reply_to.enabled', false);
    $message = practitionerReplyToSend(new PractitionerReplyToProbeMail(practitionerReplyToUser()));

    expect($message->getFrom())->toHaveCount(1)
        ->and($message->getFrom()[0]->getAddress())->toBe('contact@olithea.fr')
        ->and($message->getReplyTo())->toBeEmpty()
        ->and($message->getTo()[0]->getAddress())->toBe('client@example.test')
        ->and($message->getSubject())->toBe('Message de contrôle');
});

test('serialized queued mail keeps practitioners strictly isolated', function () {
    $first = practitionerReplyToUser([
        'email' => 'premier@example.test',
        'company_email' => 'cabinet-un@example.test',
    ]);
    $second = practitionerReplyToUser([
        'email' => 'second@example.test',
        'company_email' => 'cabinet-deux@example.test',
    ]);

    $firstMail = unserialize(serialize(new PractitionerReplyToProbeMail($first)));
    $secondMail = unserialize(serialize(new PractitionerReplyToProbeMail($second)));
    $firstMessage = practitionerReplyToSend($firstMail, 'client-un@example.test');
    $secondMessage = practitionerReplyToSend($secondMail, 'client-deux@example.test');

    expect($firstMessage->getReplyTo())->toHaveCount(1)
        ->and($firstMessage->getReplyTo()[0]->getAddress())->toBe('cabinet-un@example.test')
        ->and($secondMessage->getReplyTo())->toHaveCount(1)
        ->and($secondMessage->getReplyTo()[0]->getAddress())->toBe('cabinet-deux@example.test');
});

test('missing practitioner and self-addressed test messages remain safe', function () {
    $missingPractitioner = app(PractitionerReplyToResolver::class)->resolve(null);
    $practitioner = practitionerReplyToUser([
        'company_email' => 'meme-adresse@example.test',
    ]);
    $message = practitionerReplyToSend(
        new PractitionerReplyToProbeMail($practitioner),
        'meme-adresse@example.test'
    );
    $legacyQuestionnaire = new QuestionnaireSentMail(
        'Praticien',
        'Questionnaire',
        'https://olithea.fr/q',
        'Cliente'
    );

    expect($missingPractitioner?->address)->toBe('contact@olithea.fr')
        ->and($message->getTo()[0]->getAddress())->toBe('meme-adresse@example.test')
        ->and($message->getReplyTo())->toHaveCount(1)
        ->and($message->getReplyTo()[0]->getAddress())->toBe('meme-adresse@example.test')
        ->and($legacyQuestionnaire->hasReplyTo('contact@olithea.fr', 'Olithea'))->toBeTrue()
        ->and(practitionerReplyToCount($legacyQuestionnaire))->toBe(1);
});

test('representative appointment event billing questionnaire document campaign and newsletter mails resolve the owner', function () {
    $context = practitionerReplyToContext();

    PDF::shouldReceive('loadView')->andReturnSelf();
    PDF::shouldReceive('output')->andReturn('%PDF-1.4');

    $mails = [
        new AppointmentCreatedPatientMail($context['appointment']),
        new AppointmentEditedClientMail($context['appointment']),
        new AppointmentReminderClientMail($context['appointment']),
        new ReservationConfirmation($context['reservation']),
        new EventReminderClientMail($context['event'], $context['reservation']),
        new InvoiceMail($context['invoice'], 'Cabinet Harmonie'),
        new QuoteMail($context['quote'], 'Cabinet Harmonie'),
        new InvoicePaymentLinkMail($context['invoice'], 'Cabinet Harmonie', 'Nadine'),
        new InvoicePaymentReminderMail($context['invoice'], 'Cabinet Harmonie'),
        new QuestionnaireSentMail('Camille', 'Questionnaire', 'https://olithea.fr/q', 'Nadine', $context['practitioner']),
        new DocumentSignRequestMail($context['document'], $context['signing'], 'Nadine'),
        new OfferJourneyMessageMail($context['practitioner'], 'Sujet campagne', 'Message', 'https://olithea.fr/desinscription', 'marketing'),
        new NewsletterMail($context['newsletter'], $context['client'], 'https://olithea.fr/desinscription'),
    ];

    foreach ($mails as $mail) {
        if (method_exists($mail, 'build')) {
            $mail->build();
        }

        expect($mail->hasReplyTo('cabinet@example.test', 'Cabinet Harmonie'))->toBeTrue()
            ->and(practitionerReplyToCount($mail))->toBe(1);
    }

    expect($mails[0]->hasSubject('Confirmation de votre rendez-vous'))->toBeTrue()
        ->and($mails[3]->hasSubject('Confirmation de votre réservation'))->toBeTrue()
        ->and($mails[11]->hasSubject('Sujet campagne'))->toBeTrue()
        ->and($mails[12]->hasSubject('Des nouvelles du cabinet'))->toBeTrue()
        ->and($mails[11]->hasFrom('contact@olithea.fr', 'Olithea'))->toBeTrue()
        ->and($mails[12]->hasFrom('contact@olithea.fr', 'Olithea'))->toBeTrue();
});

test('remaining client invitations content and portal mails resolve their practitioner relation', function () {
    $practitioner = practitionerReplyToUser();
    $client = (new ClientProfile([
        'first_name' => 'Nadine',
        'last_name' => 'Cliente',
        'email' => 'nadine@example.test',
    ]))->setRelation('user', $practitioner);
    $conseil = (new App\Models\Conseil(['name' => 'Conseil']))->setRelation('user', $practitioner);
    $document = (new Document([
        'original_name' => 'document.pdf',
        'storage_path' => 'documents/document.pdf',
        'status' => 'draft',
    ]))->setRelation('owner', $practitioner);
    $signing = (new DocumentSigning([
        'token' => 'token-signature',
        'status' => 'pending',
    ]))->setRelation('document', $document);
    $training = (new App\Models\DigitalTraining(['title' => 'Formation']))->setRelation('user', $practitioner);
    $enrollment = (new App\Models\DigitalTrainingEnrollment([
        'access_token' => 'token-formation',
        'participant_email' => 'nadine@example.test',
    ]))->setRelation('training', $training);
    $voucher = (new App\Models\GiftVoucher([
        'code' => 'CADEAU-REPLY',
        'buyer_name' => 'Acheteur',
    ]))->setRelation('therapist', $practitioner);
    $community = (new App\Models\CommunityGroup(['name' => 'Communauté']))->setRelation('user', $practitioner);
    $emargement = (new App\Models\Emargement(['token' => 'token-emargement']))->setRelation('therapist', $practitioner);
    $testimonialRequest = (new App\Models\TestimonialRequest)->setRelation('therapist', $practitioner);

    $mails = [
        new App\Mail\ClientSetPasswordLink($client, 'token-espace-client'),
        new App\Mail\CommunityInviteMail($community, $client, 'https://olithea.fr/communaute'),
        new App\Mail\ConseilSentMail($client, $conseil, 'https://olithea.fr/conseil'),
        new App\Mail\DigitalTrainingAccessMail($enrollment),
        new App\Mail\DocumentSignatureLinkMail($signing),
        new App\Mail\DocumentSignedFinalMail($document, 'Nadine'),
        new App\Mail\EmargementRequestMail($emargement),
        new App\Mail\GiftVoucherBuyerMail($voucher, '%PDF-1.4'),
        new App\Mail\GiftVoucherRecipientMail($voucher, '%PDF-1.4'),
        new App\Mail\MeetingInvitation('https://olithea.fr/visio', $practitioner),
        new App\Mail\TestimonialRequestMail($testimonialRequest),
        new App\Mail\TherapistFileUploadedToClientMail($client, new App\Models\ClientFile),
        new App\Mail\TherapistMessageSentToClientMail($client, new App\Models\Message),
    ];

    foreach ($mails as $mail) {
        if (method_exists($mail, 'build')) {
            $mail->build();
        }

        expect($mail->hasReplyTo('cabinet@example.test', 'Cabinet Harmonie'))->toBeTrue()
            ->and(practitionerReplyToCount($mail))->toBe(1);
    }
});

test('client activation invitation identifies the practitioner and explains the portal', function () {
    $practitioner = practitionerReplyToUser();
    $client = ClientProfile::create([
        'user_id' => $practitioner->id,
        'first_name' => 'Nadine',
        'last_name' => 'Cliente',
        'email' => 'nadine-activation@example.test',
        'password_setup_expires_at' => now()->addDays(3),
    ]);

    $mail = new App\Mail\ClientSetPasswordLink($client, 'token-espace-client');
    $content = html_entity_decode(strip_tags($mail->render()));

    expect($content)
        ->toContain('Cabinet Harmonie vous invite à activer votre espace client sécurisé sur Olithéa')
        ->toContain('rendez-vous, documents, questionnaires ou messages')
        ->toContain('Ce lien est personnel et valable jusqu’au')
        ->toContain('Si vous n’attendiez pas cette invitation')
        ->and($mail->hasSubject('Cabinet Harmonie vous invite à activer votre espace client'))
        ->toBeTrue();
});

test('every inventoried client-facing mailable explicitly opts in', function () {
    $clientFacing = [
        App\Mail\AppointmentCreatedPatientMail::class,
        App\Mail\AppointmentEditedClientMail::class,
        App\Mail\AppointmentReminderClientMail::class,
        App\Mail\ClientSetPasswordLink::class,
        App\Mail\CommunityInviteMail::class,
        App\Mail\ConseilSentMail::class,
        App\Mail\DigitalTrainingAccessMail::class,
        App\Mail\DocumentSignatureLinkMail::class,
        App\Mail\DocumentSignedFinalMail::class,
        App\Mail\DocumentSignRequestMail::class,
        App\Mail\EmargementRequestMail::class,
        App\Mail\EventReminderClientMail::class,
        App\Mail\GiftVoucherBuyerMail::class,
        App\Mail\GiftVoucherRecipientMail::class,
        App\Mail\InvoiceMail::class,
        App\Mail\InvoicePaymentLinkMail::class,
        App\Mail\InvoicePaymentReminderMail::class,
        App\Mail\MeetingInvitation::class,
        App\Mail\NewsletterMail::class,
        App\Mail\OfferJourneyMessageMail::class,
        App\Mail\QuestionnaireSentMail::class,
        App\Mail\QuoteMail::class,
        App\Mail\ReservationConfirmation::class,
        App\Mail\TestimonialRequestMail::class,
        App\Mail\TherapistFileUploadedToClientMail::class,
        App\Mail\TherapistMessageSentToClientMail::class,
    ];

    foreach ($clientFacing as $mailable) {
        expect(class_uses_recursive($mailable))->toContain(RepliesToPractitioner::class);
    }
});

test('practitioner admin inbound and system mails are not opted in', function () {
    $excluded = [
        AppointmentCancelledByClient::class,
        AppointmentCreatedTherapistMail::class,
        ClientFileUploadedTherapistMail::class,
        ClientMessageReceivedTherapistMail::class,
        InformationRequestMail::class,
        NewReservationNotification::class,
        QuestionnaireCompletedMail::class,
        AdminNewUserNotification::class,
        WelcomeProMail::class,
    ];

    foreach ($excluded as $mailable) {
        expect(class_uses_recursive($mailable))->not->toContain(RepliesToPractitioner::class);
    }
});
