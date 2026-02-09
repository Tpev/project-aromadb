<?php

namespace App\Models;

use App\Services\JitsiJwtService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'start_date_time',
        'duration',
        'booking_required',
        'limited_spot',
        'number_of_spot',
        'associated_product',
        'image',
        'showOnPortail',
        'location',

        // Visio / Format
        'event_type',      // in_person | visio
        'visio_provider',  // external | aromamade
        'visio_url',       // lien externe si provider=external
        'visio_token',     // room token Jitsi si provider=aromamade
    ];

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
     * - otherwise AromaMade visio URL (Jitsi + JWT non-moderator)
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
     * - otherwise AromaMade visio URL (Jitsi + JWT moderator)
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
