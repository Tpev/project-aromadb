<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneySupportAudit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'before_json' => 'array',
        'after_json' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Un journal de support ne peut pas etre modifie.'));
        static::deleting(fn () => throw new \LogicException('Un journal de support ne peut pas etre supprime.'));
    }
}
