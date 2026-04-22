<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityChannel extends Model
{
    use HasFactory;

    public const TYPE_DISCUSSION = 'discussion';
    public const TYPE_ANNOUNCEMENTS = 'annonces';

    protected $fillable = [
        'community_group_id',
        'name',
        'channel_type',
        'description',
        'position',
        'is_active',
        'pinned_community_message_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(CommunityGroup::class, 'community_group_id');
    }

    public function messages()
    {
        return $this->hasMany(CommunityMessage::class)->orderBy('created_at');
    }

    public function pinnedMessage()
    {
        return $this->belongsTo(CommunityMessage::class, 'pinned_community_message_id');
    }

    public function isAnnouncements(): bool
    {
        return $this->channel_type === self::TYPE_ANNOUNCEMENTS;
    }
}
