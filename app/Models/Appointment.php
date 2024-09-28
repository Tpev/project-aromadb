<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
