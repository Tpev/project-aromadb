<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinancePayout extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'paid' => 'Payé',
        'pending' => 'En attente',
        'in_transit' => 'En route',
        'canceled' => 'Annulé',
        'failed' => 'Échoué',
    ];

    public const RECONCILIATION_LABELS = [
        'en_attente' => 'En attente',
        'partiel' => 'Partiel',
        'rapproche' => 'Rapproché',
    ];

    protected $fillable = [
        'stripe_payout_id',
        'balance_transaction_id',
        'status',
        'type',
        'method',
        'currency',
        'amount_cents',
        'arrival_date',
        'stripe_created_at',
        'automatic',
        'description',
        'statement_descriptor',
        'reconciliation_status',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'arrival_date' => 'datetime',
        'stripe_created_at' => 'datetime',
        'automatic' => 'boolean',
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getReconciliationLabelAttribute(): string
    {
        return self::RECONCILIATION_LABELS[$this->reconciliation_status] ?? 'En attente';
    }

    public function getStripeDashboardUrlAttribute(): string
    {
        return 'https://dashboard.stripe.com/payouts/' . $this->stripe_payout_id;
    }
}
