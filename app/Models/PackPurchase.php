<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
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
