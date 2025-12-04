<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterOptOut extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'newsletter_recipient_id',
        'reason',
        'unsubscribed_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    public function therapist()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recipient()
    {
        return $this->belongsTo(NewsletterRecipient::class, 'newsletter_recipient_id');
    }
}
