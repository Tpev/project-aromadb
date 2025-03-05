<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformationRequest extends Model
{
    protected $fillable = [
        'therapist_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'message',
    ];

    // Relationship: Each InformationRequest belongs to one therapist
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}
