<?php

// app/Models/DigitalTrainingEnrollment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DigitalTrainingEnrollment extends Model
{
    use HasFactory;

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_FREE_GATE = 'free_gate';

    protected $fillable = [
        'digital_training_id',
        'client_profile_id',
        'participant_name',
        'participant_email',
        'access_token',
        'token_expires_at',
        'progress_percent',
        'first_accessed_at',
        'last_accessed_at',
        'completed_at',
        'source',
        'email_communication_consent',
        'email_communication_consent_at',
    ];

    protected $casts = [
        'token_expires_at'  => 'datetime',
        'first_accessed_at' => 'datetime',
        'last_accessed_at'  => 'datetime',
        'completed_at'      => 'datetime',
        'viewed_block_ids'  => 'array',
        'email_communication_consent' => 'boolean',
        'email_communication_consent_at' => 'datetime',
    ];

    public function training()
    {
        return $this->belongsTo(DigitalTraining::class, 'digital_training_id');
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function comments()
    {
        return $this->hasMany(DigitalTrainingBlockComment::class, 'digital_training_enrollment_id');
    }

    public function sourceLabel(): string
    {
        return match ((string) $this->source) {
            self::SOURCE_FREE_GATE => 'Accès gratuit',
            self::SOURCE_MANUAL => 'Manuel',
            default => ucfirst(str_replace('_', ' ', (string) $this->source)),
        };
    }
}
