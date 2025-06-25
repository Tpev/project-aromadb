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
	 'stripe_session_id', // Ajouté pour suivre la session Stripe
	 'google_event_id',     
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
	
	    protected static function booted()
    {
        static::created(fn ($appt) => $appt->syncToGoogle());
        static::updated(fn ($appt) => $appt->syncToGoogle());
        static::deleted(fn ($appt) => $appt->removeFromGoogle());
    }
	
public function syncToGoogle(): void
{
    $therapist = $this->user;

    // Le thérapeute n’a pas connecté Google ?
    if (!$therapist || !$therapist->google_access_token) {
        return;
    }

    // On injecte son access_token dans la config Spatie
    config(['google-calendar.oauth_token' => json_decode($therapist->google_access_token, true)]);

    $eventData = [
        'name'          => 'RDV – '.$this->clientProfile->first_name,
        'startDateTime' => Carbon::parse($this->appointment_date),
        'endDateTime'   => Carbon::parse($this->appointment_date)->addMinutes($this->duration),
        'description'   => $this->notes,
    ];

    if ($this->google_event_id) {
        // Mise à jour
        Event::find($this->google_event_id)?->update($eventData);
    } else {
        // Création
        $event = Event::create($eventData);
        $this->fill(['google_event_id' => $event->id])->saveQuietly();
    }
}

public function removeFromGoogle(): void
{
    if (!$this->google_event_id || !$this->user?->google_access_token) {
        return;
    }

    config(['google-calendar.oauth_token' => json_decode($this->user->google_access_token, true)]);
    Event::find($this->google_event_id)?->delete();
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
	
	    public function meeting()
    {
        return $this->hasOne(Meeting::class);
    }
public function invoice()
{
    return $this->hasOne(Invoice::class);
}

    // Cast appointment_date to datetime
    protected $casts = [
        'appointment_date' => 'datetime',
    ];
}
