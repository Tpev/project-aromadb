<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionNote extends Model
{
    use HasFactory;

    protected $fillable = ['client_profile_id', 'user_id', 'note', 'created_at'];

    /**
     * The user (therapist) that created the session note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The client profile for the session note.
     */
    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }
}
