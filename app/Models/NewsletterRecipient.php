<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'newsletter_id',
        'client_profile_id',
        'email',
        'status',
        'unsubscribe_token',
        'sent_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'sent_at'        => 'datetime',
        'unsubscribed_at'=> 'datetime',
    ];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }
	// app/Models/NewsletterRecipient.php
public function newsletter()
{
    return $this->belongsTo(Newsletter::class);
}

// app/Models/Newsletter.php
public function user()
{
    return $this->belongsTo(User::class);
}

}
