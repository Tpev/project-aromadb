<?php

namespace App\Models;

use App\Services\JitsiJwtService;
use App\Support\EventDuration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

// app/Models/Event.php
protected $fillable = [
    'user_id',
    'name',
    'description',
    'start_date_time',
    'duration',
    'end_date_time',
    'booking_required',
    'limited_spot',
    'number_of_spot',
    'associated_product',
    'image',
    'showOnPortail',
    'location',

    // Paid events
    'collect_payment', // bool
    'price',           // float TTC base (or HT depending on your convention)
    'tax_rate',        // float %

    // Visio / Format
    'event_type',
    'visio_provider',
    'visio_url',
    'visio_token',
];

    protected $casts = [
        'start_date_time' => 'datetime',
        'end_date_time' => 'datetime',
        'duration' => 'integer',
        'booking_required' => 'boolean',
        'limited_spot' => 'boolean',
        'showOnPortail' => 'boolean',
        'collect_payment' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Event $event): void {
            if ($event->start_date_time && (int) $event->duration > 0) {
                $event->end_date_time = Carbon::parse($event->start_date_time)
                    ->addMinutes((int) $event->duration);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function associatedProduct()
    {
        return $this->belongsTo(Product::class, 'associated_product');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function calendarBlock()
    {
        return $this->hasOne(Unavailability::class);
    }

    public function getEndsAtAttribute(): ?Carbon
    {
        if ($this->end_date_time) {
            return Carbon::parse($this->end_date_time);
        }

        if (! $this->start_date_time || (int) $this->duration < 1) {
            return null;
        }

        return Carbon::parse($this->start_date_time)->addMinutes((int) $this->duration);
    }

    public function getFormattedDurationAttribute(): string
    {
        return EventDuration::format($this->duration);
    }

    public function getFormattedPeriodAttribute(): string
    {
        if (! $this->start_date_time || ! $this->ends_at) {
            return 'Date à confirmer';
        }

        $start = Carbon::parse($this->start_date_time);
        $end = $this->ends_at;

        if ($start->isSameDay($end)) {
            return 'Le '.$start->format('d/m/Y').' de '.$start->format('H:i').' à '.$end->format('H:i');
        }

        return 'Du '.$start->format('d/m/Y').' à '.$start->format('H:i')
            .' au '.$end->format('d/m/Y').' à '.$end->format('H:i');
    }

    public function isUpcoming(?Carbon $at = null): bool
    {
        $at ??= now();

        return Carbon::parse($this->start_date_time)->gt($at);
    }

    public function isOngoing(?Carbon $at = null): bool
    {
        $at ??= now();

        return Carbon::parse($this->start_date_time)->lte($at)
            && $this->ends_at?->gt($at);
    }

    public function isPast(?Carbon $at = null): bool
    {
        $at ??= now();

        return ! $this->ends_at || $this->ends_at->lte($at);
    }

    public function scopeNotEnded(Builder $query, ?Carbon $at = null): Builder
    {
        $at ??= now();
        $driver = $query->getConnection()->getDriverName();

        return $query->where(function (Builder $query) use ($at, $driver): void {
            $query->where('end_date_time', '>', $at)
                ->orWhere(function (Builder $query) use ($at, $driver): void {
                    $query->whereNull('end_date_time');

                    if ($driver === 'sqlite') {
                        $query->whereRaw("datetime(start_date_time, '+' || duration || ' minutes') > ?", [$at]);
                    } else {
                        $query->whereRaw('DATE_ADD(start_date_time, INTERVAL duration MINUTE) > ?', [$at]);
                    }
                });
        });
    }

    public function scopeEnded(Builder $query, ?Carbon $at = null): Builder
    {
        $at ??= now();
        $driver = $query->getConnection()->getDriverName();

        return $query->where(function (Builder $query) use ($at, $driver): void {
            $query->where('end_date_time', '<=', $at)
                ->orWhere(function (Builder $query) use ($at, $driver): void {
                    $query->whereNull('end_date_time');

                    if ($driver === 'sqlite') {
                        $query->whereRaw("datetime(start_date_time, '+' || duration || ' minutes') <= ?", [$at]);
                    } else {
                        $query->whereRaw('DATE_ADD(start_date_time, INTERVAL duration MINUTE) <= ?', [$at]);
                    }
                });
        });
    }

    public function isVisio(): bool
    {
        return ($this->event_type ?? 'in_person') === 'visio';
    }

    public function isAromaMadeVisio(): bool
    {
        return $this->isVisio()
            && (($this->visio_provider ?? null) === 'aromamade')
            && !empty($this->visio_token);
    }

    /**
     * Base URL for Jitsi (your visio subdomain).
     * Defaults to https://visio.aromamade.com
     */
    protected function visioBaseUrl(): string
    {
        // if you have services.jitsi.base_url you can set it (ex: https://visio.aromamade.com)
        $base = config('services.jitsi.base_url');

        if (empty($base)) {
            $domain = config('services.jitsi.domain', 'visio.aromamade.com');
            $base = 'https://' . $domain;
        }

        return rtrim($base, '/');
    }

    /**
     * Build a Jitsi JWT payload for an event.
     * We do NOT rely on appointment context here.
     */
    protected function makeEventJwt(bool $moderator): string
    {
        /** @var \App\Services\JitsiJwtService $jitsi */
        $jitsi = app(JitsiJwtService::class);

        $room = (string) $this->visio_token;

        // Therapist (host) uses current authenticated user if available,
        // otherwise fallback to event owner (user relation).
        $u = auth()->user() ?: $this->user;

        if ($moderator) {
            $displayName =
                trim(($u?->first_name ?? '') . ' ' . ($u?->last_name ?? ''))
                ?: ($u?->name ?? 'Thérapeute');

            $email = $u?->email ?? null;

            return $jitsi->generate([
                'room' => $room,
                'sub'  => config('services.jitsi.domain', 'visio.aromamade.com'),
                'context' => [
                    'user' => [
                        'id' => (string)($u?->id ?? Str::uuid()),
                        'name' => $displayName,
                        'email' => $email,
                        'moderator' => true,
                    ],
                    'group' => 'therapist',
                ],
            ]);
        }

        // Public/participant: generic non-moderator JWT
        return $jitsi->generate([
            'room' => $room,
            'sub'  => config('services.jitsi.domain', 'visio.aromamade.com'),
            'context' => [
                'user' => [
                    'id' => (string) Str::uuid(),
                    'name' => 'Participant',
                    'email' => null,
                    'moderator' => false,
                ],
                'group' => 'client',
            ],
        ]);
    }

    /**
     * Participant/public link:
     * - external url if provided
     * - otherwise Olithea visio URL (Jitsi + JWT non-moderator)
     *
     * Example:
     * https://visio.aromamade.com/{room}?jwt=...
     */
    public function getVisioPublicLinkAttribute(): ?string
    {
        if (!$this->isVisio()) return null;

        if (!empty($this->visio_url)) {
            return $this->visio_url;
        }

        if ($this->isAromaMadeVisio()) {
            $jwt = $this->makeEventJwt(false);
            return $this->visioBaseUrl() . '/' . $this->visio_token . '?jwt=' . urlencode($jwt);
        }

        return null;
    }

    /**
     * Therapist/host link:
     * - external url if provided (same link)
     * - otherwise Olithea visio URL (Jitsi + JWT moderator)
     */
    public function getVisioHostLinkAttribute(): ?string
    {
        if (!$this->isVisio()) return null;

        if (!empty($this->visio_url)) {
            return $this->visio_url;
        }

        if ($this->isAromaMadeVisio()) {
            $jwt = $this->makeEventJwt(true);
            return $this->visioBaseUrl() . '/' . $this->visio_token . '?jwt=' . urlencode($jwt);
        }

        return null;
    }

    /**
     * Backward-compatible accessor used by older blades ($event->visio_link).
     * For therapist pages, we prefer host link.
     */
    public function getVisioLinkAttribute(): ?string
    {
        return $this->visio_host_link;
    }
}
