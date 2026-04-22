<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityMessage extends Model
{
    use HasFactory;

    public const SENDER_PRACTITIONER = 'practitioner';
    public const SENDER_CLIENT = 'client';

    protected $fillable = [
        'community_group_id',
        'community_channel_id',
        'user_id',
        'client_profile_id',
        'sender_type',
        'content',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(CommunityGroup::class, 'community_group_id');
    }

    public function channel()
    {
        return $this->belongsTo(CommunityChannel::class, 'community_channel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function attachments()
    {
        return $this->hasMany(CommunityMessageAttachment::class)->orderBy('id');
    }

    public function isPinned(): bool
    {
        return (int) $this->channel?->pinned_community_message_id === (int) $this->id;
    }

    public function authorName(): string
    {
        if ($this->sender_type === self::SENDER_PRACTITIONER) {
            return $this->user?->company_name
                ?? $this->user?->name
                ?? 'Praticien';
        }

        return trim((string) (($this->clientProfile?->first_name ?? '') . ' ' . ($this->clientProfile?->last_name ?? ''))) ?: 'Membre';
    }
}
