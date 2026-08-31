<?php

use App\Models\ClientProfile;
use App\Models\DocumentNumberingChangeLog;
use App\Models\DocumentNumberingCounter;
use App\Models\DocumentNumberingSetting;
use App\Models\GiftVoucher;
use App\Models\Invoice;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\Receipt;
use App\Models\User;
use App\Notifications\InvoicePaid;
use App\Services\DocumentNumberFormatter;
use App\Services\DocumentNumberingService;
use App\Services\GiftVoucherInvoiceService;
use App\Services\InvoiceCorrectionService;
use App\Services\PackPurchaseInvoicingService;
use App\Services\ReceiptRecordingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

function numberingTestUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'license_status' => 'active',
    ], $attributes));
}

function numberingTestClient(User $user): ClientProfile
{
    return ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Client',
        'last_name' => 'Numérotation',
        'email' => 'numbering-'.uniqid().'@example.test',
    ]);
}

function enableTestNumbering(
    User $user,
    string $documentType,
    string $format,
    string $resetFrequency = 'never',
    int $nextSequence = 1
): array {
    return app(DocumentNumberingService::class)->updateConfiguration($user, $documentType, [
        'enabled' => true,
        'format' => $format,
        'reset_frequency' => $resetFrequency,
        'next_sequence' => $nextSequence,
    ], $user);
}

function createNumberingTestDocument(
    User $user,
    ClientProfile $client,
    string $type = 'invoice',
    string $date = '2026-08-31',
    array $attributes = []
): Invoice {
    $numbering = $type === 'quote'
        ? app(DocumentNumberingService::class)->allocateQuote($user, $date)
        : app(DocumentNumberingService::class)->allocateInvoice($user, $date);

    return Invoice::create(array_merge([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => $date,
        'due_date' => $date,
        'status' => 'En attente',
        'type' => $type,
        'total_amount' => 100,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 100,
    ], $numbering, $attributes));
}

test('formatter supports the customer target and the documented tokens', function (string $format, int $sequence, string $expected) {
    $date = Carbon::parse('2026-08-31');
    $formatter = app(DocumentNumberFormatter::class);

    expect($formatter->validate($format))->toBe($format)
        ->and($formatter->format($format, $date, $sequence))->toBe($expected);
})->with([
    'customer compact target' => ['{YYYY}{MM}{SEQ:4}', 282, '2026080282'],
    'invoice prefix' => ['FAC-{YYYY}-{SEQ:4}', 7, 'FAC-2026-0007'],
    'quote prefix' => ['DEV-{YY}/{MM}/{SEQ:3}', 21, 'DEV-26/08/021'],
    'padding never truncates' => ['{YYYY}/{SEQ:2}', 1234, '2026/1234'],
]);

test('formatter rejects ambiguous unsafe and unknown formats', function (string $format) {
    expect(fn () => app(DocumentNumberFormatter::class)->validate($format))
        ->toThrow(ValidationException::class);
})->with([
    'empty' => [''],
    'missing sequence' => ['FAC-{YYYY}'],
    'two sequences' => ['{SEQ}-{SEQ:4}'],
    'unknown token' => ['FAC-{DATE}-{SEQ}'],
    'duplicate year' => ['{YYYY}-{YY}-{SEQ}'],
    'unsafe character' => ['FAC:<{SEQ}>'],
]);

test('reset frequency requires enough date information to prevent future duplicates', function () {
    $user = numberingTestUser();
    $service = app(DocumentNumberingService::class);

    try {
        $service->updateConfiguration($user, DocumentNumberingService::INVOICE, [
            'enabled' => true,
            'format' => 'FAC-{SEQ:4}',
            'reset_frequency' => 'yearly',
            'next_sequence' => 1,
        ]);
        $this->fail('Annual reset without a year should fail.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('invoice_numbering_format');
    }

    try {
        $service->updateConfiguration($user, DocumentNumberingService::INVOICE, [
            'enabled' => true,
            'format' => 'FAC-{YYYY}-{SEQ:4}',
            'reset_frequency' => 'monthly',
            'next_sequence' => 1,
        ]);
        $this->fail('Monthly reset without a month should fail.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('invoice_numbering_format');
    }

    expect(DocumentNumberingSetting::count())->toBe(0);
});

test('legacy numbering remains the default and creates no optional configuration', function () {
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    Invoice::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => '2026-08-01',
        'invoice_number' => 41,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => 10,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 10,
    ]);

    $invoice = createNumberingTestDocument($user, $client);
    $quote = createNumberingTestDocument($user, $client, 'quote');

    expect($invoice->invoice_number)->toBe(42)
        ->and($invoice->custom_number)->toBeNull()
        ->and($quote->quote_number)->toStartWith('D-')
        ->and($quote->custom_number)->toBeNull()
        ->and(DocumentNumberingSetting::count())->toBe(0)
        ->and(DocumentNumberingCounter::count())->toBe(0);
});

test('customization starts at a migrated sequence and affects only future documents', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    $legacy = Invoice::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => '2026-08-30',
        'invoice_number' => 77,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => 100,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 100,
    ]);

    enableTestNumbering($user, DocumentNumberingService::INVOICE, '{YYYY}{MM}{SEQ:4}', 'never', 282);
    $firstCustom = createNumberingTestDocument($user, $client);
    $secondCustom = createNumberingTestDocument($user, $client);

    expect($legacy->fresh()->invoice_number)->toBe(77)
        ->and($legacy->fresh()->custom_number)->toBeNull()
        ->and($firstCustom->invoice_number)->toBe('2026080282')
        ->and($firstCustom->internal_invoice_number)->toBe(78)
        ->and($firstCustom->number_sequence)->toBe(282)
        ->and($secondCustom->invoice_number)->toBe('2026080283')
        ->and($secondCustom->internal_invoice_number)->toBe(79);

    enableTestNumbering($user, DocumentNumberingService::INVOICE, 'FAC-{YYYY}-{SEQ:4}', 'never', 900);
    $afterChange = createNumberingTestDocument($user, $client);

    expect($firstCustom->fresh()->invoice_number)->toBe('2026080282')
        ->and($afterChange->invoice_number)->toBe('FAC-2026-0900')
        ->and($afterChange->numbering_version)->toBe(2)
        ->and(DocumentNumberingChangeLog::where('document_type', 'invoice')->count())->toBe(2);
});

test('disabling customization restores legacy numbering only for later documents', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    enableTestNumbering($user, 'invoice', 'FAC-{SEQ:3}', 'never', 25);
    $custom = createNumberingTestDocument($user, $client);

    app(DocumentNumberingService::class)->updateConfiguration($user, 'invoice', [
        'enabled' => false,
        'format' => 'FAC-{SEQ:3}',
        'reset_frequency' => 'never',
        'next_sequence' => 26,
    ], $user);
    $legacy = createNumberingTestDocument($user, $client);

    expect($custom->fresh()->invoice_number)->toBe('FAC-025')
        ->and($custom->fresh()->internal_invoice_number)->toBe(1)
        ->and($legacy->invoice_number)->toBe(2)
        ->and($legacy->custom_number)->toBeNull()
        ->and(DocumentNumberingSetting::where('user_id', $user->id)->value('enabled'))->toBeFalse();
});

test('monthly yearly and never reset policies keep isolated counters', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');

    $monthlyUser = numberingTestUser();
    $monthlyClient = numberingTestClient($monthlyUser);
    enableTestNumbering($monthlyUser, 'invoice', '{YYYY}{MM}-{SEQ:2}', 'monthly', 8);
    $monthlyAugust = createNumberingTestDocument($monthlyUser, $monthlyClient, 'invoice', '2026-08-31');
    $monthlySeptember = createNumberingTestDocument($monthlyUser, $monthlyClient, 'invoice', '2026-09-01');

    $yearlyUser = numberingTestUser();
    $yearlyClient = numberingTestClient($yearlyUser);
    enableTestNumbering($yearlyUser, 'invoice', '{YYYY}-{SEQ:2}', 'yearly', 3);
    $yearly2026 = createNumberingTestDocument($yearlyUser, $yearlyClient, 'invoice', '2026-12-31');
    $yearly2027 = createNumberingTestDocument($yearlyUser, $yearlyClient, 'invoice', '2027-01-01');

    $neverUser = numberingTestUser();
    $neverClient = numberingTestClient($neverUser);
    enableTestNumbering($neverUser, 'invoice', 'N-{SEQ}', 'never', 5);
    $never2026 = createNumberingTestDocument($neverUser, $neverClient, 'invoice', '2026-12-31');
    $never2027 = createNumberingTestDocument($neverUser, $neverClient, 'invoice', '2027-01-01');

    expect($monthlyAugust->invoice_number)->toBe('202608-08')
        ->and($monthlySeptember->invoice_number)->toBe('202609-01')
        ->and($yearly2026->invoice_number)->toBe('2026-03')
        ->and($yearly2027->invoice_number)->toBe('2027-01')
        ->and($never2026->invoice_number)->toBe('N-5')
        ->and($never2027->invoice_number)->toBe('N-6');
});

test('invoice and quote sequences are independent while credit notes share the invoice sequence', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    enableTestNumbering($user, 'invoice', 'FAC-{SEQ:4}', 'never', 20);
    enableTestNumbering($user, 'quote', 'DEV-{SEQ:4}', 'never', 400);

    $invoice = createNumberingTestDocument($user, $client);
    $invoice->items()->create([
        'type' => 'custom',
        'label' => 'Consultation',
        'description' => 'Consultation',
        'quantity' => 1,
        'unit_price' => 100,
        'tax_rate' => 0,
        'tax_amount' => 0,
        'total_price' => 100,
        'total_price_with_tax' => 100,
    ]);
    $quote = createNumberingTestDocument($user, $client, 'quote');
    $creditNote = app(InvoiceCorrectionService::class)
        ->create($invoice, 'credit_note', 'Erreur de facturation', $user);

    expect($invoice->invoice_number)->toBe('FAC-0020')
        ->and($quote->quote_number)->toBe('DEV-0400')
        ->and($creditNote->type)->toBe('credit_note')
        ->and($creditNote->invoice_number)->toBe('FAC-0021')
        ->and($creditNote->numbering_family)->toBe('invoice');
});

test('an allocation rolls back with its document transaction and does not consume a number', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    enableTestNumbering($user, 'invoice', 'FAC-{SEQ:3}', 'never', 10);

    try {
        DB::transaction(function () use ($user, $client) {
            createNumberingTestDocument($user, $client);
            throw new RuntimeException('Simulated document failure');
        });
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Simulated document failure');
    }

    $invoice = createNumberingTestDocument($user, $client);

    expect($invoice->invoice_number)->toBe('FAC-010')
        ->and(Invoice::count())->toBe(1)
        ->and(DocumentNumberingCounter::value('next_sequence'))->toBe(11);
});

test('duplicate next number is rejected before changing configuration', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    enableTestNumbering($user, 'invoice', 'FAC-{SEQ:3}', 'never', 10);
    createNumberingTestDocument($user, $client);

    try {
        enableTestNumbering($user, 'invoice', 'FAC-{SEQ:3}', 'never', 10);
        $this->fail('The duplicate next number should fail.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('invoice_numbering_next_sequence');
    }

    expect(DocumentNumberingSetting::where('user_id', $user->id)->value('version'))->toBe(1)
        ->and(DocumentNumberingChangeLog::where('user_id', $user->id)->count())->toBe(1);
});

test('receipts and payment status retain the customized invoice number', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    enableTestNumbering($user, 'invoice', '{YYYY}/{SEQ:4}', 'never', 282);
    $invoice = createNumberingTestDocument($user, $client);

    $receipt = app(ReceiptRecordingService::class)->recordInvoicePayment(
        $invoice,
        100,
        '2026-08-31',
        'card'
    );

    expect($receipt->invoice_number)->toBe('2026/0282')
        ->and($invoice->fresh()->status)->toBe('Payée')
        ->and($invoice->fresh()->safe_document_number)->toBe('2026_0282')
        ->and((float) $invoice->fresh()->solde_restant)->toBe(0.0)
        ->and((new InvoicePaid($invoice->fresh()))->toDatabase($user)['invoice_number'])->toBe('2026/0282');
});

test('manual invoice and quote creation routes use their configured sequences', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    enableTestNumbering($user, 'invoice', 'FAC-{YYYY}-{SEQ:3}', 'never', 31);
    enableTestNumbering($user, 'quote', 'DEV-{YYYY}-{SEQ:3}', 'never', 71);

    $this->actingAs($user)->post(route('invoices.store'), [
        'client_profile_id' => $client->id,
        'invoice_date' => '2026-08-31',
        'items' => [[
            'type' => 'custom',
            'label' => 'Consultation',
            'description' => 'Consultation',
            'quantity' => 1,
            'unit_price' => 80,
            'tax_rate' => 0,
        ]],
    ])->assertSessionHasNoErrors();

    $this->post(route('invoices.storeQuote'), [
        'client_profile_id' => $client->id,
        'quote_date' => '2026-08-31',
        'items' => [[
            'type' => 'custom',
            'label' => 'Accompagnement',
            'description' => 'Accompagnement',
            'quantity' => 1,
            'unit_price' => 120,
            'tax_rate' => 0,
        ]],
    ])->assertSessionHasNoErrors();

    $invoice = Invoice::where('type', 'invoice')->firstOrFail();
    $quote = Invoice::where('type', 'quote')->firstOrFail();

    expect($invoice->invoice_number)->toBe('FAC-2026-031')
        ->and($quote->quote_number)->toBe('DEV-2026-071');
});

test('pack and gift voucher automatic invoices use custom numbering without breaking receipts', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();
    $client = numberingTestClient($user);
    enableTestNumbering($user, 'invoice', 'AUTO-{SEQ:3}', 'never', 50);

    $pack = PackProduct::create([
        'user_id' => $user->id,
        'name' => 'Pack suivi',
        'description' => 'Pack suivi',
        'price' => 100,
        'tax_rate' => 0,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);
    $purchase = PackPurchase::create([
        'user_id' => $user->id,
        'pack_product_id' => $pack->id,
        'client_profile_id' => $client->id,
        'status' => 'active',
        'payment_mode' => 'one_time',
        'payment_state' => 'paid',
    ]);
    $packInvoice = app(PackPurchaseInvoicingService::class)->ensureInvoiceForPurchase($purchase);

    $voucher = GiftVoucher::create([
        'user_id' => $user->id,
        'code' => 'AM-NUM-0001',
        'original_amount_cents' => 9000,
        'remaining_amount_cents' => 9000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Acheteur Numérotation',
        'buyer_email' => 'voucher-numbering@example.test',
        'source' => 'manual',
        'sale_channel' => 'offline_manual',
        'sale_status' => 'paid',
    ]);
    $voucherInvoice = app(GiftVoucherInvoiceService::class)->createSaleInvoice($voucher, 'card');

    expect($packInvoice?->invoice_number)->toBe('AUTO-050')
        ->and($voucherInvoice?->invoice_number)->toBe('AUTO-051')
        ->and(Receipt::where('invoice_id', $voucherInvoice?->id)->value('invoice_number'))->toBe('AUTO-051')
        ->and($voucherInvoice?->fresh()->status)->toBe('Payée');
});

test('company form clearly warns and requires explicit confirmation for any numbering change', function () {
    Carbon::setTestNow('2026-08-31 10:00:00');
    $user = numberingTestUser();

    $this->actingAs($user)
        ->get(route('profile.editCompanyInfo'))
        ->assertOk()
        ->assertSee('Numérotation')
        ->assertSee('uniquement les futurs documents')
        ->assertSee('Prochaine séquence à utiliser')
        ->assertSee('{YYYY}{MM}{SEQ:4}');

    $payload = [
        'numbering_settings_submitted' => 1,
        'invoice_numbering_enabled' => 1,
        'invoice_numbering_format' => '{YYYY}{MM}{SEQ:4}',
        'invoice_numbering_reset_frequency' => 'monthly',
        'invoice_numbering_next_sequence' => 282,
        'quote_numbering_enabled' => 0,
        'quote_numbering_format' => 'DEV-{YYYY}-{SEQ:4}',
        'quote_numbering_reset_frequency' => 'never',
        'quote_numbering_next_sequence' => 1,
    ];

    $this->from(route('profile.editCompanyInfo'))
        ->put(route('profile.updateCompanyInfo'), $payload)
        ->assertRedirect(route('profile.editCompanyInfo'))
        ->assertSessionHasErrors('confirm_numbering_change');

    expect(DocumentNumberingSetting::count())->toBe(0);

    $this->put(route('profile.updateCompanyInfo'), array_merge($payload, [
        'confirm_numbering_change' => 1,
    ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.editCompanyInfo'));

    $setting = DocumentNumberingSetting::where('user_id', $user->id)
        ->where('document_type', 'invoice')
        ->firstOrFail();

    expect($setting->enabled)->toBeTrue()
        ->and($setting->format)->toBe('{YYYY}{MM}{SEQ:4}')
        ->and($setting->reset_frequency)->toBe('monthly')
        ->and(DocumentNumberingCounter::where('user_id', $user->id)->value('next_sequence'))->toBe(282)
        ->and(DocumentNumberingChangeLog::where('user_id', $user->id)->count())->toBe(1);
});
