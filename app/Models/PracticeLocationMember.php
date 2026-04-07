<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeLocationMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_location_id',
        'user_id',
        'role',
        'accepted_at',
        'added_by_user_id',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function practiceLocation()
    {
        return $this->belongsTo(PracticeLocation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
}
