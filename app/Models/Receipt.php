<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class Receipt extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_id',
        'invoice_number',
        'encaissement_date',
        'client_name',
        'nature',
        'amount_ht',
        'amount_ttc',
        'payment_method',
        'direction',       // credit|debit
        'source',          // payment|manual|correction|refund
        'provider',
        'provider_reference',
        'note',
        'locked_at',
        'record_number',
        'is_reversal',
        'reversal_of_id',
    ];

    // ⚠️ IMPORTANT : on ne mélange pas fillable + guarded sur les mêmes champs
    // Ici, on laisse record_number dans fillable (utile pour imports/seed),
    // mais on le génère automatiquement si non fourni.
    protected $guarded = [];

    protected $casts = [
        'record_number'     => 'integer',
        'encaissement_date' => 'date',
        'accounting_date'   => 'date',
        'is_reversal'       => 'boolean',
        'locked_at'         => 'datetime',
        'amount_ht'         => 'decimal:2',
        'amount_ttc'        => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function (Receipt $model) {
            // scellage à la création
            if (empty($model->locked_at)) {
                $model->locked_at = now();
            }

            // ✅ record_number "per user" (anti-concurrence)
            // - si record_number déjà fourni (import/seed), on ne touche pas
            if (!empty($model->record_number)) {
                return;
            }

            if (empty($model->user_id)) {
                throw new \RuntimeException('Receipt: user_id requis pour générer record_number.');
            }

            DB::transaction(function () use ($model) {
                // lock toutes les lignes du user pour sérialiser l'incrément
                $max = DB::table('receipts')
                    ->where('user_id', $model->user_id)
                    ->lockForUpdate()
                    ->max('record_number');

                $model->record_number = ((int) $max) + 1;
            }, 3);
        });

        // ✅ IMMUTABILITÉ
        static::updating(function () {
            throw new \RuntimeException('Livre de recettes immuable : utilisez une contre-écriture.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Impossible de supprimer une écriture : utilisez une contre-écriture.');
        });
    }

    // Labels (utile dans tes vues)
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'transfer' => 'Virement',
            'card'     => 'Carte',
            'check'    => 'Chèque',
            'cash'     => 'Espèces',
            'other'    => 'Autre',
            default    => ucfirst((string)$this->payment_method),
        };
    }

    // Relations contre-passations
    /**
     * Corrections cancel the original entry in its period, even when an older
     * reversal was entered with a different date. Keep the stored dates intact.
     * Refunds and unlinked entries retain their own cash movement date.
     */
    public function scopeWithAccountingDate(Builder $query): Builder
    {
        $rows = DB::table('receipts as recorded_receipts')
            ->leftJoin('receipts as original_receipt', function (JoinClause $join) {
                $join->on('original_receipt.id', '=', 'recorded_receipts.reversal_of_id')
                    ->on('original_receipt.user_id', '=', 'recorded_receipts.user_id')
                    ->where('recorded_receipts.source', 'correction')
                    ->where('recorded_receipts.is_reversal', true);
            })
            ->select('recorded_receipts.*')
            ->selectRaw('COALESCE(original_receipt.encaissement_date, recorded_receipts.encaissement_date) as accounting_date');

        return $query->fromSub($rows, 'receipts');
    }

    public function original()
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversals()
    {
        return $this->hasMany(self::class, 'reversal_of_id');
    }

    // signed amount avec direction credit|debit
    public function getSignedAmountTtcAttribute(): float
    {
        return $this->direction === 'debit'
            ? -1 * (float) $this->amount_ttc
            : (float) $this->amount_ttc;
    }

    public function scopeNetSumTtc($q)
    {
        return $q->selectRaw("SUM(CASE WHEN direction='credit' THEN amount_ttc ELSE -amount_ttc END) as total");
    }

    // Relations classiques
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
