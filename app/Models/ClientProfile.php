<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Policies\ClientProfilePolicy;
use Illuminate\Notifications\Notifiable;

// ✅ ADD: password reset support (client)
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Notifications\ResetPassword;

class ClientProfile extends Authenticatable implements CanResetPasswordContract
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'user_id','first_name','last_name','email','phone',
        'address','birthdate','notes',
        'first_name_billing','last_name_billing',
        'password',                    // new
        'password_setup_token_hash',   // coming next
        'password_setup_expires_at',
        'company_id',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'password'      => 'hashed',
        'last_login_at' => 'datetime',
        'password_setup_expires_at' => 'datetime',
    ];

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

    public function testimonialRequests()
    {
        return $this->hasMany(TestimonialRequest::class);
    }

    /**
     * Get the testimonials for the client profile.
     */
    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }

    public function conseilsSent()
    {
        return $this->belongsToMany(\App\Models\Conseil::class, 'client_conseil', 'client_profile_id', 'conseil_id')
                    ->withPivot('sent_at', 'token')
                    ->withTimestamps();
    }

    public function metrics()
    {
        // If your foreign key is client_profile_id
        return $this->hasMany(Metric::class, 'client_profile_id');
    }

    public function clientFiles()
    {
        return $this->hasMany(ClientFile::class);
    }

    public function messages()
    {
        return $this->hasMany(\App\Models\Message::class);
    }

    public function company()
    {
        return $this->belongsTo(CorporateClient::class, 'company_id');
    }

    // Dans la classe ClientProfile
    protected $appends = ['is_corporate']; // optionnel mais pratique

    public function getIsCorporateAttribute(): bool
    {
        return !is_null($this->company_id);
    }

    // app/Models/ClientProfile.php
    public function trainingEnrollments()
    {
        return $this->hasMany(DigitalTrainingEnrollment::class);
    }

    /* ---------------------------------------------------------
       ✅ FIX: Force reset email URL to CLIENT reset route
       This ensures the email goes to /client/reset-password/{token}
       instead of the normal /reset-password/{token}.
    --------------------------------------------------------- */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new class($token) extends ResetPassword {
            protected function resetUrl($notifiable)
            {
                return route('client.password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->email,
                ]);
            }
        });
    }
}
