<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
protected $fillable = [
    'name',
    'start_time',
    'duration',
    'participant_email',
    'client_profile_id',
    'room_token',

];
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
