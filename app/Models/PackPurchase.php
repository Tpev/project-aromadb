<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PackPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pack_product_id',
        'client_profile_id',
        'purchased_at',
        'expires_at',
        'status',
        'notes',
		'stripe_session_id',
		'stripe_subscription_id',
		'stripe_customer_id',
		'payment_mode',
		'payment_state',
		'installments_total',
		'installments_paid',
		'installment_amount_cents',
		'purchase_type',
		'digital_training_id',
		'activated_at',
		'completed_at',
		'canceled_requested_at',
		'canceled_effective_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
        'canceled_requested_at' => 'datetime',
        'canceled_effective_at' => 'datetime',
        'installments_total' => 'integer',
        'installments_paid' => 'integer',
        'installment_amount_cents' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pack()
    {
        return $this->belongsTo(PackProduct::class, 'pack_product_id');
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function items()
    {
        return $this->hasMany(PackPurchaseItem::class);
    }

    public function installments()
    {
        return $this->hasMany(PurchaseInstallment::class, 'pack_purchase_id');
    }

    public function digitalTraining()
    {
        return $this->belongsTo(DigitalTraining::class, 'digital_training_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'pack_purchase_id');
    }

    public function scopeEligibleForConsumption($query)
    {
        if (!Schema::hasColumn('pack_purchases', 'payment_mode') || !Schema::hasColumn('pack_purchases', 'payment_state')) {
            return $query;
        }

        return $query->where(function ($q) {
            $q->whereNull('payment_mode')
                ->orWhere('payment_mode', 'one_time')
                ->orWhere(function ($q2) {
                    $q2->where('payment_mode', 'installments')
                        ->whereIn('payment_state', ['active', 'completed', 'cancel_scheduled']);
                });
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Consomme des crédits pour une prestation donnée (ex: 1 massage).
     * À brancher plus tard à la création de RDV/facture.
     */
    public function consumeProduct(int $productId, int $qty = 1): void
    {
        DB::transaction(function () use ($productId, $qty) {

            $this->refresh();

            if ($this->status !== 'active') {
                throw new \RuntimeException("Pack non actif.");
            }

            if (($this->payment_mode === 'installments')
                && !in_array((string) $this->payment_state, ['active', 'completed', 'cancel_scheduled'], true)
            ) {
                throw new \RuntimeException("Paiement du pack en plusieurs fois non valide.");
            }

            if ($this->isExpired()) {
                $this->update(['status' => 'expired']);
                throw new \RuntimeException("Pack expiré.");
            }

            $line = $this->items()->where('product_id', $productId)->lockForUpdate()->first();

            if (!$line) {
                throw new \RuntimeException("Ce produit n'est pas inclus dans ce pack.");
            }

            if ($line->quantity_remaining < $qty) {
                throw new \RuntimeException("Crédits insuffisants.");
            }

            $line->update([
                'quantity_remaining' => $line->quantity_remaining - $qty,
            ]);

            // Si tout est à 0 => exhausted
            $remaining = (int) $this->items()->sum('quantity_remaining');
            if ($remaining <= 0) {
                $this->update(['status' => 'exhausted']);
            }
        });
    }
}
