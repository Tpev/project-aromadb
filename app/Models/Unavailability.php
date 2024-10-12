<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unavailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'reason',
    ];

    /**
     * The user that owns the unavailability.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
	    protected $casts = [
        'start_date' => 'datetime',        
		'end_date' => 'datetime',
    ];
}
