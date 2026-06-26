<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperPdpReceivedInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'connection_id',
        'user_id',
        'super_pdp_invoice_id',
        'super_pdp_company_id',
        'direction',
        'external_id',
        'invoice_number',
        'invoice_date',
        'seller_name',
        'buyer_name',
        'currency_code',
        'total_with_vat',
        'latest_event_code',
        'latest_event_text',
        'latest_event_at',
        'raw_payload',
        'last_synced_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_with_vat' => 'decimal:4',
        'latest_event_at' => 'datetime',
        'raw_payload' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(SuperPdpConnection::class, 'connection_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
