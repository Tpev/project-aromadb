<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Receipt extends Model
{
    protected $fillable = [
        'user_id','invoice_id','invoice_number','encaissement_date','client_name',
        'nature','amount_ht','amount_ttc','payment_method','direction','source','note','locked_at'
    ];
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'transfer' => 'Virement',
            'card'     => 'Carte',
            'check'    => 'Chèque',
            'cash'     => 'Espèces',
            'other'    => 'Autre',
            default    => ucfirst((string) $this->payment_method),
        };
    }
	    protected $guarded = [
        // Empêche la mass assignment sur record_number
        'record_number',
    ];
protected static function booted()
{
    // Seal on insert
    static::creating(function($model){
        $model->locked_at = now();
    });

    // 🔐 After insert: assign sequential record_number = id
    static::created(function ($receipt) {
        if (empty($receipt->record_number)) {
            DB::table('receipts')
                ->where('id', $receipt->id)
                ->update(['record_number' => $receipt->id]);

            // keep the in-memory instance in sync
            $receipt->record_number = $receipt->id;
        }
    });

    // Immutability guards
    static::updating(function(){
        throw new \RuntimeException('Livre de recettes immuable : utilisez une contre-écriture.');
    });
    static::deleting(function(){
        throw new \RuntimeException('Impossible de supprimer une écriture : utilisez une contre-écriture.');
    });
}

public function original()
{
    return $this->belongsTo(self::class, 'reversal_of_id');
}
public function reversals()
{
    return $this->hasMany(self::class, 'reversal_of_id');
}
    protected $casts = [
        'record_number'     => 'integer',
        'encaissement_date' => 'date',
        'is_reversal'       => 'boolean',
        'locked_at'         => 'datetime',
    ];

public function getSignedAmountTtcAttribute()
{
    return $this->direction === 'out' ? -1 * $this->amount_ttc : $this->amount_ttc;
}

public function scopeNetSumTtc($q)
{
    return $q->selectRaw("SUM(CASE WHEN direction='out' THEN -amount_ttc ELSE amount_ttc END) as total");
}

    public function invoice(){ return $this->belongsTo(Invoice::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
