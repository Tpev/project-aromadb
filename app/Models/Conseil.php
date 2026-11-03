<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conseil extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'image',
        'attachment',
        'tag',
        'links',
        'user_id',
    ];

    protected $casts = ['links' => 'array'];

    public function getDisplayLinksAttribute(): array
    {
        return collect($this->links ?? [])
            ->filter(fn ($link) => is_array($link)
                && is_string($link['url'] ?? null)
                && Str::isUrl($link['url'], ['http', 'https']))
            ->map(fn ($link) => [
                'url' => $link['url'],
                'label' => is_string($link['label'] ?? null) && trim($link['label']) !== ''
                    ? $link['label'] : $link['url'],
            ])->values()->all();
    }

    /**
     * Get the user that owns the conseil.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

public function clients()
{
    return $this->belongsToMany(\App\Models\ClientProfile::class, 'client_conseil', 'conseil_id', 'client_profile_id')
                ->withPivot('sent_at', 'token')
                ->withTimestamps();
}


}
