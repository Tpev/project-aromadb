<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 

class Appointment extends Model
{
    use HasFactory;

protected $fillable = [
    'client_profile_id',
    'user_id',
    'appointment_date',
    'status',
    'notes',
    'type',
    'duration',
    'product_id',
];

    // Automatically generate a token when creating a new appointment
    public static function boot()
    {
        parent::boot();

        // Listen for the creating event to generate the token
        static::creating(function ($appointment) {
            $appointment->token = Str::random(64); // Generate a random 64-character string
        });
    }
// Add relationship to Product
public function product()
{
    return $this->belongsTo(Product::class);
}

    /**
     * The user (therapist) that created the appointment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The client profile for the appointment.
     */
    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }
	// In Appointment.php model
public function invoice()
{
    return $this->hasOne(Invoice::class);
}

    // Cast appointment_date to datetime
    protected $casts = [
        'appointment_date' => 'datetime',
    ];
}
