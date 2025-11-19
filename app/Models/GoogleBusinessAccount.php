<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoogleBusinessAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'account_display_name',
        'location_id',
        'location_title',
        'refresh_token',
        'access_token',
        'access_token_expires_at',
        'last_synced_at',
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
        'last_synced_at'          => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
