<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuperPdpConnection extends Model
{
    use HasFactory;

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_AUTHORIZATION_STARTED = 'authorization_started';
    public const STATUS_CONNECTED = 'connected';
    public const STATUS_ERROR = 'error';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'user_id',
        'environment',
        'status',
        'receiving_invoices_enabled',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'token_type',
        'scope',
        'super_pdp_company_id',
        'super_pdp_company_name',
        'super_pdp_company_number',
        'super_pdp_company_number_scheme',
        'last_error',
        'metadata',
        'connected_at',
        'last_synced_at',
        'revoked_at',
    ];

    protected $casts = [
        'receiving_invoices_enabled' => 'boolean',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'metadata' => 'array',
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receivedInvoices(): HasMany
    {
        return $this->hasMany(SuperPdpReceivedInvoice::class, 'connection_id');
    }

    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED && filled($this->refresh_token);
    }
}
