<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conseil extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'image',
        'attachment',
        'tag',
        'user_id',
    ];

    /**
     * Get the user that owns the conseil.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

public function clients()
{
    return $this->belongsToMany(\App\Models\ClientProfile::class, 'client_conseil', 'conseil_id', 'client_profile_id')
                ->withPivot('sent_at', 'token')
                ->withTimestamps();
}


}
