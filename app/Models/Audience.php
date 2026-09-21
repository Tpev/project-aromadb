<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clients()
    {
        return $this->belongsToMany(ClientProfile::class, 'audience_client_profile')
                    ->withTimestamps();
    }

    public function newsletterContacts()
    {
        return $this->belongsToMany(NewsletterContact::class, 'audience_newsletter_contact')->withTimestamps();
    }

    public function getContactsCountAttribute(): int
    {
        return (int) ($this->clients_count ?? $this->clients()->count())
            + (int) ($this->newsletter_contacts_count ?? $this->newsletterContacts()->count());
    }
}
