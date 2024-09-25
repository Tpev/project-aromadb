<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageViewLog extends Model
{
    protected $fillable = [
        'url', 
        'viewed_at', 
        'ip_address', 
        'session_id', 
        'referrer',
		'user_agent'
    ];

    // Disable automatic timestamps since we manage 'viewed_at'
    public $timestamps = false;
}
