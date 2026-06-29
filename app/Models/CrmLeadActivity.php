<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmLeadActivity extends Model
{
    public const TYPES = [
        'call' => 'Appel',
        'email' => 'Email',
        'meeting' => 'Rendez-vous',
        'sms' => 'SMS',
        'note' => 'Note',
        'task' => 'Tache',
    ];

    public const DIRECTIONS = [
        'outbound' => 'Sortant',
        'inbound' => 'Entrant',
        'internal' => 'Interne',
    ];

    protected $fillable = [
        'crm_lead_id',
        'user_id',
        'type',
        'direction',
        'subject',
        'body',
        'occurred_at',
        'due_at',
        'completed_at',
        'outcome',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(CrmLead::class, 'crm_lead_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }
}
