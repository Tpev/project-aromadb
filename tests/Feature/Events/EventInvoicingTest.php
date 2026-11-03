<?php

use App\Models\ClientProfile;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Reservation;
use App\Models\User;
use App\Services\EventInvoiceService;
use App\Services\InvoiceCorrectionService;
use App\Services\InvoiceLifecycleService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Mail::fake();
    Http::preventStrayRequests();
    $this->therapist = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $this->client = ClientProfile::create([
        'user_id' => $this->therapist->id, 'first_name' => 'Alice', 'last_name' => 'Martin',
        'email' => 'alice@example.test',
    ]);
    $this->event = Event::create([
        'user_id' => $this->therapist->id, 'name' => 'Atelier respiration',
        'start_date_time' => '2026-07-14 10:00:00', 'duration' => 90,
        'location' => 'Salle des ateliers', 'booking_required' => true,
        'limited_spot' => false, 'showOnPortail' => true,
        'price' => 50, 'tax_rate' => 20, 'collect_payment' => true,
    ]);
    $this->reservation = Reservation::create([
        'event_id' => $this->event->id, 'full_name' => 'Alice Martin',
        'email' => ' Alice@EXAMPLE.test ', 'phone' => '0600000000', 'status' => 'confirmed',
    ]);
    $this->actingAs($this->therapist);
});

function eventInvoicePayload($context, array $overrides = []): array
{
    return array_replace([
        'client_profile_id' => $context->client->id,
        'invoice_date' => now()->toDateString(),
        'event_reservation_id' => $context->reservation->id,
        'items' => [app(EventInvoiceService::class)->item($context->reservation->fresh())],
    ], $overrides);
}

test('each participant can open invoice preparation from desktop and mobile without creating documents', function () {
    $url = route('invoices.create', ['event_reservation_id' => $this->reservation->id]);
    foreach (['events.show', 'mobile.events.show'] as $route) {
        $this->get(route($route, $this->event))->assertOk()->assertSee('Facturer')->assertSee($url, false);
    }
    $this->get($url)->assertOk()
        ->assertViewHas('selectedClient', fn ($client) => $client->id === $this->client->id)
        ->assertViewHas('eventInvoiceItem', fn ($item) => $item['label'] === 'Participation — Atelier respiration'
            && $item['service_date'] === '2026-07-14' && $item['unit_price'] == 50 && $item['tax_rate'] == 20)
        ->assertSee('Salle des ateliers')->assertSee('name="event_reservation_id"', false);
    expect(Invoice::count())->toBe(0)->and(Receipt::count())->toBe(0);
});

test('saving an event invoice persists its client line dates totals and immutable event snapshot', function () {
    $this->post(route('invoices.store'), eventInvoicePayload($this))->assertSessionHasNoErrors();
    $invoice = Invoice::sole();
    $item = $invoice->items->sole();
    expect($invoice->client_profile_id)->toBe($this->client->id)
        ->and($invoice->event_reservation_id)->toBe($this->reservation->id)
        ->and($invoice->appointment_id)->toBeNull()
        ->and($invoice->event_snapshot['name'])->toBe('Atelier respiration')
        ->and($invoice->event_snapshot['participant_name'])->toBe('Alice Martin')
        ->and($invoice->event_snapshot['location'])->toBe('Salle des ateliers')
        ->and((float) $invoice->total_amount)->toBe(50.0)
        ->and((float) $invoice->total_tax_amount)->toBe(10.0)
        ->and((float) $invoice->total_amount_with_tax)->toBe(60.0)
        ->and($invoice->status)->toBe('En attente')
        ->and($invoice->invoice_number)->not->toBeEmpty()
        ->and($item->label)->toBe('Participation — Atelier respiration')
        ->and($item->service_date->toDateString())->toBe('2026-07-14')
        ->and($item->billing_description)->toContain('10:00', '11:30', 'Salle des ateliers');
    $this->event->update(['name' => 'Nouveau nom', 'start_date_time' => '2027-01-01 09:00', 'price' => 99, 'location' => 'Autre salle']);
    $this->reservation->update(['full_name' => 'Nom modifié']);
    foreach (['invoices.show', 'mobile.invoices.show', 'invoices.edit'] as $route) {
        $this->get(route($route, $invoice))->assertOk()->assertSee('Atelier respiration')->assertSee('Alice Martin')
            ->assertSee('14/07/2026')->assertSee('Salle des ateliers')->assertDontSee('Autre salle');
    }
    $pdfHtml = view('invoices.pdf', ['invoice' => $invoice->fresh(['items', 'user', 'clientProfile'])])->render();
    expect($pdfHtml)->toContain('Atelier respiration', '14/07/2026', 'Salle des ateliers', 'Alice Martin');
    $this->get(route('invoices.pdf', $invoice))->assertOk()->assertHeader('content-type', 'application/pdf');
    expect(Receipt::count())->toBe(0);
    Mail::assertNothingSent();
});

test('event details survive event deletion without deleting its invoice', function () {
    $this->post(route('invoices.store'), eventInvoicePayload($this))->assertSessionHasNoErrors();
    $invoice = Invoice::sole();
    $this->event->delete();
    expect($invoice->fresh()->event_reservation_id)->toBeNull()
        ->and($invoice->fresh()->event_snapshot['name'])->toBe('Atelier respiration');
    $this->get(route('invoices.show', $invoice))->assertOk()->assertSee('Atelier respiration');
});

test('a multi day event prepopulates and saves its service period', function () {
    $this->event->update(['duration' => 1800]);
    $payload = eventInvoicePayload($this);
    expect($payload['items'][0]['service_date'])->toBeNull();
    $this->post(route('invoices.store'), $payload)->assertSessionHasNoErrors();
    $item = Invoice::sole()->items->sole();
    expect($item->service_period_start->toDateString())->toBe('2026-07-14')
        ->and($item->service_period_end->toDateString())->toBe('2026-07-15');
});

test('missing event pricing falls back to the owned associated product and supports zero price', function () {
    $product = Product::create(['user_id' => $this->therapist->id, 'name' => 'Séance', 'price' => 35, 'tax_rate' => 5.5]);
    $this->event->update(['price' => null, 'tax_rate' => 5.5, 'associated_product' => $product->id]);
    $item = app(EventInvoiceService::class)->item($this->reservation->fresh());
    expect($item['unit_price'])->toBe(35.0)->and($item['tax_rate'])->toBe(5.5);
    $this->event->update(['associated_product' => null]);
    $this->post(route('invoices.store'), eventInvoicePayload($this))->assertSessionHasNoErrors();
    expect((float) Invoice::sole()->total_amount_with_tax)->toBe(0.0);
});

test('paid reservations use the original amount and create exactly one receipt without charging again', function () {
    $this->reservation->forceFill([
        'status' => 'paid', 'amount_ttc' => 60, 'currency' => 'eur', 'stripe_payment_intent_id' => 'pi_event_paid',
        'payment_confirmation_requested_at' => now()->subDays(5),
    ])->save();
    $this->event->update(['price' => 120]);
    $payload = eventInvoicePayload($this);
    expect($payload['items'][0]['unit_price'])->toBe(50.0);
    $this->post(route('invoices.store'), $payload)->assertSessionHasNoErrors();
    $invoice = Invoice::sole();
    $receipt = Receipt::sole();
    expect($invoice->status)->toBe('Payée')->and($invoice->solde_restant)->toBe(0.0)
        ->and($invoice->isEditable())->toBeFalse()
        ->and($receipt->invoice_id)->toBe($invoice->id)
        ->and((float) $receipt->amount_ttc)->toBe(60.0)
        ->and((float) $receipt->amount_ht)->toBe(50.0)
        ->and($receipt->encaissement_date->toDateString())->toBe(now()->subDays(5)->toDateString())
        ->and($receipt->provider_reference)->toBe('pi_event_paid');
    $this->post(route('invoices.store'), $payload)->assertRedirect(route('invoices.show', $invoice));
    expect(Invoice::count())->toBe(1)->and(Receipt::count())->toBe(1);
    Http::assertNothingSent();
    Mail::assertNothingSent();
});

test('legacy paid reservations require a real receipt date and roll back completely on missing data', function () {
    $this->reservation->update(['status' => 'paid', 'amount_ttc' => 60, 'currency' => 'eur']);
    $this->get(route('invoices.create', ['event_reservation_id' => $this->reservation->id]))
        ->assertOk()->assertSee('Date du paiement reçu');
    $payload = eventInvoicePayload($this);
    $this->post(route('invoices.store'), $payload)->assertSessionHasErrors('event_payment_date');
    expect(Invoice::count())->toBe(0)->and(Receipt::count())->toBe(0);
    $this->post(route('invoices.store'), array_merge($payload, ['event_payment_date' => '2026-07-01']))->assertSessionHasNoErrors();
    expect(Receipt::sole()->encaissement_date->toDateString())->toBe('2026-07-01')
        ->and(Invoice::sole()->status)->toBe('Payée');
});

test('paid invoice total cannot diverge from recorded payment or accept a future receipt date', function () {
    $this->reservation->update(['status' => 'paid', 'amount_ttc' => 60, 'currency' => 'eur']);
    $payload = eventInvoicePayload($this, ['event_payment_date' => now()->toDateString()]);
    $payload['items'][0]['unit_price'] = 80;
    $this->post(route('invoices.store'), $payload)->assertSessionHasErrors('items');
    expect(Invoice::count())->toBe(0)->and(Receipt::count())->toBe(0);
    $this->post(route('invoices.store'), eventInvoicePayload($this, ['event_payment_date' => now()->addDay()->toDateString()]))
        ->assertSessionHasErrors('event_payment_date');
    expect(Invoice::count())->toBe(0);
});

test('a payment already recorded on an ordinary invoice cannot be recorded again for an event', function () {
    $ordinaryPayload = eventInvoicePayload($this);
    unset($ordinaryPayload['event_reservation_id']);
    $this->post(route('invoices.store'), $ordinaryPayload)->assertSessionHasNoErrors();
    $ordinaryInvoice = Invoice::sole();
    expect($ordinaryInvoice->event_reservation_id)->toBeNull()->and($ordinaryInvoice->event_snapshot)->toBeNull();
    app(\App\Services\ReceiptRecordingService::class)->recordInvoicePayment(
        $ordinaryInvoice, 60, now()->toDateString(), provider: 'stripe', providerReference: 'pi_already_recorded',
    );
    $this->reservation->forceFill([
        'status' => 'paid', 'amount_ttc' => 60, 'currency' => 'eur',
        'stripe_payment_intent_id' => 'pi_already_recorded', 'payment_confirmation_requested_at' => now(),
    ])->save();
    $this->post(route('invoices.store'), eventInvoicePayload($this))->assertSessionHasErrors('event_reservation_id');
    expect(Invoice::count())->toBe(1)->and(Receipt::count())->toBe(1)
        ->and($this->reservation->fresh()->invoice)->toBeNull();
});

test('pending cancelled failed or unsupported paid reservations cannot generate invoices', function ($attributes) {
    $this->reservation->forceFill($attributes)->save();
    $this->get(route('invoices.create', ['event_reservation_id' => $this->reservation->id]))
        ->assertRedirect(route('events.show', $this->event));
    $this->post(route('invoices.store'), eventInvoicePayload($this))->assertSessionHasErrors('event_reservation_id');
    expect(Invoice::count())->toBe(0)->and(Receipt::count())->toBe(0);
})->with([
    'pending' => [['status' => 'pending_payment']],
    'canceled' => [['status' => 'canceled']],
    'cancelled attendance' => [['cancelled_at' => '2026-07-01']],
    'failed' => [['status' => 'failed']],
    'active checkout' => [['stripe_session_id' => 'cs_pending']],
    'missing paid amount' => [['status' => 'paid', 'amount_ttc' => null]],
    'foreign currency' => [['status' => 'paid', 'amount_ttc' => 60, 'currency' => 'usd']],
]);

test('reservation ownership client identity and appointment separation are enforced', function () {
    $other = User::factory()->create(['is_therapist' => true]);
    $this->actingAs($other)->get(route('invoices.create', ['event_reservation_id' => $this->reservation->id]))->assertNotFound();
    $foreignClient = ClientProfile::create(['user_id' => $other->id, 'first_name' => 'Other', 'last_name' => 'Client', 'email' => 'alice@example.test']);
    $this->post(route('invoices.store'), eventInvoicePayload($this, ['client_profile_id' => $foreignClient->id]))->assertNotFound();
    $this->actingAs($this->therapist)->post(route('invoices.store'), eventInvoicePayload($this, ['client_profile_id' => $foreignClient->id]))
        ->assertSessionHasErrors('client_profile_id');
    $wrongClient = ClientProfile::create(['user_id' => $this->therapist->id, 'first_name' => 'Wrong', 'last_name' => 'Client', 'email' => 'wrong@example.test']);
    $this->post(route('invoices.store'), eventInvoicePayload($this, ['client_profile_id' => $wrongClient->id]))->assertSessionHasErrors('client_profile_id');
    expect(Invoice::count())->toBe(0);
});

test('new participant client creation can be followed by invoice creation without mail', function () {
    $this->reservation->update(['email' => 'new@example.test', 'full_name' => 'Nora Petit']);
    $url = route('invoices.create', ['event_reservation_id' => $this->reservation->id]);
    $this->get($url)->assertOk()->assertSee('Créer le dossier client du participant');
    $result = $this->postJson(route('reservations.createClient', [$this->event, $this->reservation]))->assertCreated();
    $clientId = $result->json('client_profile_id');
    $this->get($url)->assertViewHas('selectedClient', fn ($client) => $client->id === $clientId);
    $this->post(route('invoices.store'), eventInvoicePayload($this, ['client_profile_id' => $clientId]))->assertSessionHasNoErrors();
    expect(Invoice::sole()->client_profile_id)->toBe($clientId);
    Mail::assertNothingSent();
});

test('duplicate client emails require an explicit choice and reopening an invoice never creates another', function () {
    ClientProfile::create(['user_id' => $this->therapist->id, 'first_name' => 'Same', 'last_name' => 'Email', 'email' => 'alice@example.test']);
    $url = route('invoices.create', ['event_reservation_id' => $this->reservation->id]);
    $this->get($url)->assertOk()->assertViewHas('selectedClient', null)->assertSee('Sélectionnez le dossier client');
    $payload = eventInvoicePayload($this);
    $this->post(route('invoices.store'), $payload)->assertSessionHasNoErrors();
    $invoice = Invoice::sole();
    $this->get($url)->assertRedirect(route('invoices.show', $invoice));
    $this->post(route('invoices.store'), array_merge($payload, ['allow_additional_invoice' => 1, 'additional_invoice_reason' => 'Repeated click']))
        ->assertRedirect(route('invoices.show', $invoice));
    expect(Invoice::count())->toBe(1);
    foreach (['events.show', 'mobile.events.show'] as $route) {
        $this->get(route($route, $this->event))->assertOk()->assertSee('Voir la facture')->assertDontSee($url, false);
    }
    $this->reservation->forceFill(['cancelled_at' => now()])->save();
    $this->get(route('events.show', $this->event))->assertOk()->assertSee('Voir la facture');
});

test('draft edits retain event context and cannot move billing to a different participant', function () {
    $payload = eventInvoicePayload($this);
    $this->post(route('invoices.store'), $payload)->assertSessionHasNoErrors();
    $invoice = Invoice::sole();
    $snapshot = $invoice->event_snapshot;
    unset($payload['event_reservation_id']);
    $payload['items'][0]['unit_price'] = 40;
    $payload['event_snapshot'] = ['name' => 'Forged event'];
    $this->put(route('invoices.update', $invoice), $payload)->assertSessionHasNoErrors();
    expect($invoice->fresh()->event_snapshot)->toBe($snapshot)
        ->and((float) $invoice->fresh()->total_amount_with_tax)->toBe(48.0);
    $payload['client_profile_id'] = null;
    $this->put(route('invoices.update', $invoice), $payload)->assertSessionHasErrors('client_profile_id');
});

test('corrections retain event details without claiming the unique reservation link', function ($kind) {
    $this->post(route('invoices.store'), eventInvoicePayload($this))->assertSessionHasNoErrors();
    $invoice = Invoice::sole();
    app(InvoiceLifecycleService::class)->finalize($invoice, $this->therapist);
    $correction = app(InvoiceCorrectionService::class)->create($invoice, $kind, 'Correction de la participation', $this->therapist);
    expect($correction->event_snapshot)->toBe($invoice->event_snapshot)
        ->and($correction->event_reservation_id)->toBeNull()
        ->and($this->reservation->fresh()->invoice->id)->toBe($invoice->id);
})->with(['credit_note', 'replacement']);
