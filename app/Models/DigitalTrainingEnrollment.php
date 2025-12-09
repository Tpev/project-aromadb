<?php

// app/Models/DigitalTrainingEnrollment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DigitalTrainingEnrollment extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'token_expires_at'  => 'datetime',
        'first_accessed_at' => 'datetime',
        'last_accessed_at'  => 'datetime',
        'completed_at'      => 'datetime',
    ];

    public function training()
    {
        return $this->belongsTo(DigitalTraining::class, 'digital_training_id');
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }
}
