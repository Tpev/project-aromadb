<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\ClientProfile;
use App\Models\CorporateClient;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_profile_id',
        'pack_purchase_id',
        'corporate_client_id',
        'appointment_id',
        'original_invoice_id',
        'correction_kind',
        'correction_reason',
        'user_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'total_tax_amount',
        'total_amount_with_tax',
        'status',
        'notes',
        'invoice_number',
    	'sent_at',	// Add this line
		'finalized_at',
		'recipient_snapshot',
		'payment_link', // Add this line
        'last_payment_reminder_sent_at',
        'payment_reminder_count',
		'type',
		'quote_number',
        'custom_number',
        'numbering_family',
        'number_sequence',
        'number_period',
        'numbering_version',
        'global_discount_type',
        'global_discount_value',
        'global_discount_amount_ht',
    ];

protected $attributes = [
    'type' => 'invoice',
];

public function isQuote()
{
    return $this->type === 'quote';
}

public function isCreditNote(): bool
{
    return $this->type === 'credit_note';
}

public function isInvoiceDocument(): bool
{
    return in_array($this->type ?? 'invoice', ['invoice', 'credit_note'], true);
}

    /**
     * L'utilisateur (thérapeute) qui a créé la facture.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le profil client associé à la facture.
     */
    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    /**
     * Obtenir les éléments de cette facture.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
public function appointment()
{
    return $this->belongsTo(Appointment::class);
}

public function originalInvoice()
{
    return $this->belongsTo(self::class, 'original_invoice_id');
}

public function corrections()
{
    return $this->hasMany(self::class, 'original_invoice_id');
}

public function activityLogs()
{
    return $this->hasMany(InvoiceActivityLog::class)->latest();
}

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'finalized_at' => 'datetime',
        'recipient_snapshot' => 'array',
        'last_payment_reminder_sent_at' => 'datetime',
        'number_sequence' => 'integer',
        'numbering_version' => 'integer',
    ];

public function getInvoiceNumberAttribute($value)
{
    if (($this->attributes['type'] ?? 'invoice') !== 'quote'
        && filled($this->attributes['custom_number'] ?? null)) {
        return $this->attributes['custom_number'];
    }

    return $value;
}

public function getQuoteNumberAttribute($value)
{
    if (($this->attributes['type'] ?? null) === 'quote'
        && filled($this->attributes['custom_number'] ?? null)) {
        return $this->attributes['custom_number'];
    }

    return $value;
}

public function getDisplayNumberAttribute(): string
{
    return (string) ($this->isQuote() ? $this->quote_number : $this->invoice_number);
}

public function getSafeDocumentNumberAttribute(): string
{
    $safe = preg_replace('/[^\pL\pN._-]+/u', '_', $this->display_number);
    $safe = trim((string) $safe, '._-');

    return $safe !== '' ? $safe : (string) $this->id;
}

public function getInternalInvoiceNumberAttribute(): ?int
{
    $value = $this->getRawOriginal('invoice_number');

    return is_null($value) ? null : (int) $value;
}
	
	
public function receipts()
{
    return $this->hasMany(Receipt::class);
}

public function getTotalEncaisseAttribute(): float
{
    // total TTC encaissé = somme des credits - debits
    $credit = $this->receipts()->where('direction','credit')->sum('amount_ttc');
    $debit  = $this->receipts()->where('direction','debit')->sum('amount_ttc');
    return (float) ($credit - $debit);
}

public function getSoldeRestantAttribute(): float
{
    $ttc = (float) $this->total_amount_with_tax;
    return max(0, $ttc - $this->total_encaisse);
}	

public function normalizedStatus(): string
{
    return Str::lower(Str::ascii(trim((string) $this->status)));
}

public function hasPositiveNetReceipt(): bool
{
    if ($this->relationLoaded('receipts')) {
        $net = $this->receipts->sum(fn (Receipt $receipt) => $receipt->signed_amount_ttc);
    } else {
        $net = (float) $this->receipts()
            ->selectRaw("COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount_ttc ELSE -amount_ttc END), 0) AS net")
            ->value('net');
    }

    return $net > 0.001;
}

public function isLockedForEditing(): bool
{
    if ($this->isQuote()) {
        return false;
    }

    if ($this->isCreditNote() || $this->finalized_at || $this->sent_at) {
        return true;
    }

    if (in_array($this->normalizedStatus(), ['payee', 'partiellement payee', 'paid', 'partially paid'], true)) {
        return true;
    }

    if ($this->hasPositiveNetReceipt()) {
        return true;
    }

    $total = (float) $this->total_amount_with_tax;

    return $total > 0.001 && $this->solde_restant <= 0.001;
}

public function isEditable(): bool
{
    return ! $this->isLockedForEditing();
}

public function getRecipientDataAttribute(): array
{
    return $this->recipient_snapshot
        ?: app(\App\Services\InvoiceRecipientSnapshotService::class)->current($this);
}

public function getDocumentLabelAttribute(): string
{
    return $this->isCreditNote() ? 'Avoir' : ($this->isQuote() ? 'Devis' : 'Facture');
}

public function getAppointmentBillingStatusAttribute(): string
{
    if ($this->hasPositiveNetReceipt() && $this->solde_restant > 0.001) {
        return 'Partiellement réglée';
    }

    if (((float) $this->total_amount_with_tax > 0.001 && $this->solde_restant <= 0.001)
        || in_array($this->normalizedStatus(), ['payee', 'paid'], true)) {
        return 'Réglée';
    }

    return 'En attente de règlement';
}

public function canSendPaymentReminder(): bool
{
    if (($this->type ?? 'invoice') !== 'invoice') {
        return false;
    }

    if (!$this->sent_at) {
        return false;
    }

    if ($this->solde_restant <= 0.001) {
        return false;
    }

    if ($this->sent_at->gt(now()->subDay())) {
        return false;
    }

    if ($this->last_payment_reminder_sent_at && $this->last_payment_reminder_sent_at->gt(now()->subDay())) {
        return false;
    }

    return true;
}

public function nextPaymentReminderAt()
{
    if (!$this->sent_at) {
        return null;
    }

    $availableAt = $this->sent_at->copy()->addDay();

    if ($this->last_payment_reminder_sent_at) {
        $availableAt = $availableAt->max($this->last_payment_reminder_sent_at->copy()->addDay());
    }

    return $availableAt;
}
public function corporateClient()
{
    return $this->belongsTo(CorporateClient::class, 'corporate_client_id');
}

public function packPurchase()
{
    return $this->belongsTo(PackPurchase::class, 'pack_purchase_id');
}
	
}
