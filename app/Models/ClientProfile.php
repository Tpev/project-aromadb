<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Policies\ClientProfilePolicy;

class ClientProfile extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields
    protected $fillable = ['user_id', 'first_name', 'last_name', 'email', 'phone', 'address', 'birthdate', 'notes'];
	   // Register policy
    protected static $policies = [
        ClientProfile::class => ClientProfilePolicy::class,
    ];

    /**
     * The user that owns the client profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the appointments for the client profile.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the session notes for the client profile.
     */
    public function sessionNotes()
    {
        return $this->hasMany(SessionNote::class);
    }

    /**
     * Get the invoices for the client profile.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}