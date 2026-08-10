<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentActivity extends Model
{
    protected $fillable = [
        'appointment_id', 'action', 'actor_type', 'actor_id', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
