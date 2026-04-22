<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channels()
    {
        return $this->hasMany(CommunityChannel::class)->orderBy('position')->orderBy('id');
    }

    public function members()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function activeMembers()
    {
        return $this->members()->where('status', CommunityMember::STATUS_ACTIVE);
    }

    public function invitedMembers()
    {
        return $this->members()->where('status', CommunityMember::STATUS_INVITED);
    }

    public function messages()
    {
        return $this->hasMany(CommunityMessage::class);
    }

    public function participantProfiles()
    {
        return $this->belongsToMany(ClientProfile::class, 'community_members')
            ->withPivot(['status', 'invited_at', 'joined_at'])
            ->withTimestamps();
    }

    public function defaultChannel(): ?CommunityChannel
    {
        return $this->channels->first();
    }
}
