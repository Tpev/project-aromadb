<?php

use App\Models\ClientProfile;
use App\Models\CorporateClient;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceCorrectionService;
use App\Services\InvoiceLifecycleService;
use App\Services\InvoiceRecipientSnapshotService;
use App\Services\ReceiptRecordingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function lifecycleInvoiceContext(string $email = 'invoice-lifecycle@example.test'): array
{
    $user = User::factory()->create([
        'email' => $email,
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Nadine',
        'last_name' => 'Historique',
        'email' => 'nadine-'.uniqid().'@example.test',
        'phone' => '0600000000',
        'address' => '10 rue des Archives',
    ]);
    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Séance initiale',
        'description' => 'Description initiale',
        'price' => 100,
        'tax_rate' => 20,
        'duration' => 60,
        'dans_le_cabinet' => true,
    ]);

    return compact('user', 'client', 'product');
}

function lifecycleInvoice(array $context, array $attributes = []): Invoice
{
    static $number = 15000;

    $invoice = Invoice::create(array_merge([
        'user_id' => $context['user']->id,
        'client_profile_id' => $context['client']->id,
        'invoice_date' => '2026-07-14',
        'due_date' => '2026-07-30',
        'invoice_number' => ++$number,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => 100,
        'total_tax_amount' => 20,
        'total_amount_with_tax' => 120,
    ], $attributes));

    $invoice->items()->create([
        'type' => 'product',
        'product_id' => $context['product']->id,
        'label' => $context['product']->name,
        'description' => $context['product']->description,
        'description_snapshot' => $context['product']->description,
        'quantity' => 1,
        'unit_price' => 100,
        'tax_rate' => 20,
        'tax_amount' => 20,
        'total_price' => 100,
        'total_price_with_tax' => 120,
    ]);

    return $invoice;
}

test('draft invoices stay editable while sent paid partial and receipt-backed invoices are locked', function () {
    $context = lifecycleInvoiceContext();
    $draft = lifecycleInvoice($context);
    $sent = lifecycleInvoice($context, ['sent_at' => now()]);
    $paid = lifecycleInvoice($context, ['status' => 'Payée']);
    $partial = lifecycleInvoice($context, ['status' => 'Partiellement payée']);
    $quote = lifecycleInvoice($context, ['type' => 'quote', 'status' => 'Payée', 'sent_at' => now()]);
    $receiptBacked = lifecycleInvoice($context);

    app(ReceiptRecordingService::class)->recordInvoicePayment(
        $receiptBacked,
        20,
        now()->toDateString(),
        'cash'
    );

    expect($draft->isEditable())->toBeTrue()
        ->and($sent->isEditable())->toBeFalse()
        ->and($paid->isEditable())->toBeFalse()
        ->and($partial->isEditable())->toBeFalse()
        ->and($receiptBacked->fresh()->isEditable())->toBeFalse()
        ->and($quote->isEditable())->toBeTrue();
});

test('finalized invoices reject edit update and deletion on the server', function () {
    $context = lifecycleInvoiceContext('invoice-locked-routes@example.test');
    $invoice = lifecycleInvoice($context, ['sent_at' => now(), 'finalized_at' => now()]);

    $this->actingAs($context['user'])
        ->get(route('invoices.edit', $invoice))
        ->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('error');

    $this->put(route('invoices.update', $invoice), ['notes' => 'Tentative'])
        ->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('error');

    $this->delete(route('invoices.destroy', $invoice))
        ->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('error');

    expect($invoice->fresh())->not->toBeNull()
        ->and($invoice->fresh()->notes)->toBeNull()
        ->and($invoice->activityLogs()->whereIn('event', [
            'edit_denied',
            'update_denied',
            'delete_denied',
        ])->count())->toBe(3);
});

test('desktop and mobile hide editing actions for locked invoices', function () {
    $context = lifecycleInvoiceContext('invoice-action-visibility@example.test');
    $draft = lifecycleInvoice($context);
    $locked = lifecycleInvoice($context, ['sent_at' => now(), 'finalized_at' => now()]);

    $this->actingAs($context['user'])
        ->get(route('invoices.show', $draft))
        ->assertOk()
        ->assertSee(route('invoices.edit', $draft), false);

    $this->get(route('mobile.invoices.show', $draft))
        ->assertOk()
        ->assertSee(route('invoices.edit', $draft), false);

    $this->get(route('invoices.show', $locked))
        ->assertOk()
        ->assertDontSee(route('invoices.edit', $locked), false)
        ->assertSee('Facture finalisée');

    $this->get(route('mobile.invoices.show', $locked))
        ->assertOk()
        ->assertDontSee(route('invoices.edit', $locked), false)
        ->assertSee('Document finalisé');
});

test('manual sending and payment actions record the practitioner actor', function () {
    Mail::fake();
    $context = lifecycleInvoiceContext('invoice-activity-actor@example.test');
    $sentInvoice = lifecycleInvoice($context);
    $paidInvoice = lifecycleInvoice($context);

    $this->actingAs($context['user'])
        ->post(route('invoices.sendEmail', $sentInvoice))
        ->assertRedirect(route('invoices.show', $sentInvoice));

    $this->put(route('invoices.markAsPaid', $paidInvoice), [
        'encaissement_date' => now()->toDateString(),
        'payment_method' => 'cash',
        'amount_ttc' => 20,
        'nature' => 'service',
    ])->assertRedirect(route('invoices.show', $paidInvoice));

    expect($sentInvoice->activityLogs()->where('event', 'sent')->firstOrFail()->user_id)
        ->toBe($context['user']->id)
        ->and($sentInvoice->activityLogs()->where('event', 'email_sent')->firstOrFail()->user_id)
        ->toBe($context['user']->id)
        ->and($paidInvoice->activityLogs()->where('event', 'payment_recorded')->firstOrFail()->user_id)
        ->toBe($context['user']->id);
});

test('invoice ownership cannot be bypassed through direct URLs', function () {
    $context = lifecycleInvoiceContext('invoice-owner@example.test');
    $invoice = lifecycleInvoice($context);
    $intruder = User::factory()->create([
        'email' => 'invoice-intruder@example.test',
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($intruder)->get(route('invoices.edit', $invoice))->assertForbidden();
    $this->put(route('invoices.update', $invoice), [])->assertForbidden();
    $this->delete(route('invoices.destroy', $invoice))->assertForbidden();
});

test('finalization refreshes then freezes recipient and line snapshots', function () {
    $context = lifecycleInvoiceContext('invoice-snapshot@example.test');
    $invoice = lifecycleInvoice($context);

    app(InvoiceRecipientSnapshotService::class)->capture($invoice, true);
    $context['client']->update([
        'first_name' => 'Nadine avant envoi',
        'email' => 'avant-envoi@example.test',
    ]);

    app(InvoiceLifecycleService::class)->finalize($invoice, $context['user'], 'sent');

    $context['client']->update([
        'first_name' => 'Nadine après envoi',
        'email' => 'apres-envoi@example.test',
    ]);
    $context['product']->update(['name' => 'Nouveau nom catalogue', 'tax_rate' => 5.5]);

    $frozen = $invoice->fresh(['user', 'clientProfile.company', 'items.product', 'items.inventoryItem']);
    $item = $frozen->items->first();
    $pdfHtml = view('invoices.pdf', ['invoice' => $frozen])->render();

    expect($frozen->recipient_data['client_name'])->toContain('Nadine avant envoi')
        ->and($frozen->recipient_data['email'])->toBe('avant-envoi@example.test')
        ->and($item->name)->toBe('Séance initiale')
        ->and($item->vat_rate)->toBe(20.0)
        ->and($pdfHtml)->toContain('Nadine avant envoi')
        ->and($pdfHtml)->not->toContain('Nadine après envoi')
        ->and($pdfHtml)->toContain('Séance initiale')
        ->and($pdfHtml)->not->toContain('Nouveau nom catalogue');
});

test('invoice pdf displays the business client siret', function () {
    $context = lifecycleInvoiceContext('invoice-corporate-siret@example.test');
    $company = CorporateClient::create([
        'user_id' => $context['user']->id,
        'name' => 'Aromates Conseil',
        'siret' => '123 456 789 00012',
        'billing_address' => '25 avenue des Entreprises',
        'billing_zip' => '75008',
        'billing_city' => 'Paris',
        'billing_email' => 'compta@aromates-conseil.example.test',
    ]);
    $invoice = lifecycleInvoice($context, [
        'client_profile_id' => null,
        'corporate_client_id' => $company->id,
    ]);
    app(InvoiceRecipientSnapshotService::class)->capture($invoice, true);
    $company->update(['siret' => '999 999 999 99999']);

    $pdfHtml = view('invoices.pdf', [
        'invoice' => $invoice->fresh(['user', 'corporateClient', 'items.product']),
    ])->render();

    expect($pdfHtml)
        ->toContain('Aromates Conseil')
        ->toContain('SIRET : 123 456 789 00012')
        ->not->toContain('999 999 999 99999');
});

test('invoice pdf displays the siret of a company attached to an individual client', function () {
    $context = lifecycleInvoiceContext('invoice-linked-company-siret@example.test');
    $company = CorporateClient::create([
        'user_id' => $context['user']->id,
        'name' => 'Société Cliente Liée',
        'siret' => '987 654 321 00019',
    ]);
    $context['client']->update(['company_id' => $company->id]);
    $invoice = lifecycleInvoice($context);

    $pdfHtml = view('invoices.pdf', [
        'invoice' => $invoice->fresh(['user', 'clientProfile.company', 'items.product']),
    ])->render();

    expect($pdfHtml)
        ->toContain('Société Cliente Liée')
        ->toContain('SIRET : 987 654 321 00019');
});

test('invoice pdf omits the siret line when the business client siret is absent or blank', function () {
    $context = lifecycleInvoiceContext('invoice-empty-corporate-siret@example.test');
    $company = CorporateClient::create([
        'user_id' => $context['user']->id,
        'name' => 'Entreprise sans SIRET',
        'siret' => null,
    ]);
    $invoiceWithoutSiret = lifecycleInvoice($context, [
        'client_profile_id' => null,
        'corporate_client_id' => $company->id,
    ]);
    $withoutSiretHtml = view('invoices.pdf', [
        'invoice' => $invoiceWithoutSiret->fresh(['user', 'corporateClient', 'items.product']),
    ])->render();

    $company->update(['siret' => '   ']);
    $invoiceWithBlankSiret = lifecycleInvoice($context, [
        'client_profile_id' => null,
        'corporate_client_id' => $company->id,
    ]);
    $withBlankSiretHtml = view('invoices.pdf', [
        'invoice' => $invoiceWithBlankSiret->fresh(['user', 'corporateClient', 'items.product']),
    ])->render();

    expect($withoutSiretHtml)->not->toContain('SIRET :')
        ->and($withBlankSiretHtml)->not->toContain('SIRET :');
});

test('legacy invoices without a snapshot render through a live-data fallback and can be backfilled safely', function () {
    $context = lifecycleInvoiceContext('invoice-legacy-snapshot@example.test');
    $invoice = lifecycleInvoice($context, ['recipient_snapshot' => null]);

    expect($invoice->fresh()->recipient_data['client_name'])->toContain('Nadine Historique');

    $this->artisan('invoices:backfill-recipient-snapshots', ['--dry-run' => true])
        ->assertSuccessful();
    expect($invoice->fresh()->recipient_snapshot)->toBeNull();

    $this->artisan('invoices:backfill-recipient-snapshots')->assertSuccessful();
    expect($invoice->fresh()->recipient_snapshot['client_name'])->toContain('Nadine Historique');
});

test('a paid invoice requires an avoir before a linked replacement', function () {
    $context = lifecycleInvoiceContext('invoice-correction@example.test');
    $original = lifecycleInvoice($context);
    app(ReceiptRecordingService::class)->recordInvoicePayment(
        $original,
        120,
        now()->toDateString(),
        'card'
    );

    $service = app(InvoiceCorrectionService::class);
    expect(fn () => $service->create($original->fresh(), 'replacement', 'Erreur de libellé', $context['user']))
        ->toThrow(ValidationException::class);

    $creditNote = $service->create($original->fresh(), 'credit_note', 'Annulation du document erroné', $context['user']);
    $replacement = $service->create($original->fresh(), 'replacement', 'Correction du libellé', $context['user']);

    expect($creditNote->type)->toBe('credit_note')
        ->and($creditNote->original_invoice_id)->toBe($original->id)
        ->and($creditNote->finalized_at)->not->toBeNull()
        ->and($creditNote->isEditable())->toBeFalse()
        ->and($replacement->type)->toBe('invoice')
        ->and($replacement->original_invoice_id)->toBe($original->id)
        ->and($replacement->isEditable())->toBeTrue()
        ->and($original->fresh()->status)->toBe('Payée')
        ->and($original->fresh()->items()->count())->toBe(1)
        ->and($original->fresh()->corrections()->count())->toBe(2);

    $this->actingAs($context['user'])
        ->get(route('mobile.invoices.show', $creditNote))
        ->assertOk()
        ->assertSee('Avoir')
        ->assertSee('Document finalisé')
        ->assertDontSee('Encaissements');

    expect(fn () => $service->create($original->fresh(), 'credit_note', 'Second avoir', $context['user']))
        ->toThrow(ValidationException::class)
        ->and(fn () => $service->create($original->fresh(), 'replacement', 'Seconde rectification', $context['user']))
        ->toThrow(ValidationException::class);
});
