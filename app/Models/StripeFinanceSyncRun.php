<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinanceSyncRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'started_at',
        'finished_at',
        'records_synced',
        'summary',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'records_synced' => 'integer',
        'summary' => 'array',
    ];
}
