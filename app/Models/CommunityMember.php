<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityMember extends Model
{
    use HasFactory;

    public const STATUS_INVITED = 'invited';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REMOVED = 'removed';

    protected $fillable = [
        'community_group_id',
        'client_profile_id',
        'status',
        'invited_at',
        'invitation_email_sent_at',
        'joined_at',
        'removed_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'invitation_email_sent_at' => 'datetime',
        'joined_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(CommunityGroup::class, 'community_group_id');
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInvited(): bool
    {
        return $this->status === self::STATUS_INVITED;
    }

    public function isRemoved(): bool
    {
        return $this->status === self::STATUS_REMOVED;
    }
}
