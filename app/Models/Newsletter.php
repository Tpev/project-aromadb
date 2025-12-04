<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'subject',
        'preheader',
        'from_name',
        'from_email',
        'content_json',
        'status',
        'scheduled_at',
        'sent_at',
        'recipients_count',
		'background_color',
		 'audience_id',  
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipients()
    {
        return $this->hasMany(NewsletterRecipient::class);
    }

    // Helper
    public function getBlocksAttribute(): array
    {
        if (empty($this->content_json)) {
            return [];
        }

        return json_decode($this->content_json, true) ?: [];
    }
	public function audience()
{
    return $this->belongsTo(Audience::class);
}

}
