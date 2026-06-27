<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmLead extends Model
{
    use SoftDeletes;

    public const STAGES = [
        'new' => [
            'label' => 'New',
            'description' => 'Nouveaux leads a qualifier.',
            'accent' => '#2563eb',
            'probability' => 10,
        ],
        'need_contact_info' => [
            'label' => 'Need Contact Info',
            'description' => 'Coordonnees ou contexte incomplets.',
            'accent' => '#9333ea',
            'probability' => 10,
        ],
        'contacted' => [
            'label' => 'Contacted',
            'description' => 'Premier contact effectue.',
            'accent' => '#0891b2',
            'probability' => 25,
        ],
        'presentation_ok' => [
            'label' => 'Presentation OK',
            'description' => 'Demo faite, interet confirme.',
            'accent' => '#7c3aed',
            'probability' => 45,
        ],
        'onboarding_ok' => [
            'label' => 'Onboarding OK',
            'description' => 'Pret pour la mise en place.',
            'accent' => '#d97706',
            'probability' => 65,
        ],
        'free_trial' => [
            'label' => 'Free Trial',
            'description' => 'Essai gratuit en cours.',
            'accent' => '#dc2626',
            'probability' => 80,
        ],
        'referencement_gratuit' => [
            'label' => 'Referencement Gratuit',
            'description' => 'Referencement offert avant upgrade.',
            'accent' => '#0f766e',
            'probability' => 85,
        ],
        'won' => [
            'label' => 'Won',
            'description' => 'Client gagne.',
            'accent' => '#16a34a',
            'probability' => 100,
        ],
        'lost' => [
            'label' => 'Lost',
            'description' => 'Lead perdu.',
            'accent' => '#64748b',
            'probability' => 0,
        ],
    ];

    public const FRENCH_STAGE_LABELS = [
        'new' => 'Nouveau',
        'need_contact_info' => 'Infos contact manquantes',
        'contacted' => 'Contacte',
        'presentation_ok' => 'Presentation OK',
        'onboarding_ok' => 'Onboarding OK',
        'free_trial' => 'Essai gratuit',
        'referencement_gratuit' => 'Referencement gratuit',
        'won' => 'Gagne',
        'lost' => 'Perdu',
    ];

    public const LICENSE_OPTIONS = [
        'new_free' => ['label' => 'Gratuit', 'arr' => 0],
        'new_trial' => ['label' => 'Essai', 'arr' => 0],
        'new_starter_mensuelle' => ['label' => 'Starter mensuelle', 'arr' => 118.80],
        'new_starter_annuelle' => ['label' => 'Starter annuelle', 'arr' => 108.90],
        'new_pro_mensuelle' => ['label' => 'Pro mensuelle', 'arr' => 358.80],
        'new_pro_annuelle' => ['label' => 'Pro annuelle', 'arr' => 328.90],
        'new_premium_mensuelle' => ['label' => 'Premium mensuelle', 'arr' => 598.80],
        'new_premium_annuelle' => ['label' => 'Premium annuelle', 'arr' => 548.90],
    ];

    public const DEFAULT_SOURCES = [
        'Website',
        'Referral',
        'Outbound',
        'Event',
        'Partner',
        'Email campaign',
        'Phone',
        'Other',
    ];

    protected $fillable = [
        'full_name',
        'company',
        'email',
        'phone',
        'source',
        'referral_source',
        'expected_license_type',
        'actual_license_type',
        'stage',
        'pipeline_order',
        'estimated_value',
        'probability',
        'expected_close_date',
        'next_follow_up_at',
        'last_touch_at',
        'converted_at',
        'lost_at',
        'lost_reason',
        'tags',
        'notes',
        'created_by_user_id',
        'owner_user_id',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'next_follow_up_at' => 'datetime',
        'last_touch_at' => 'datetime',
        'converted_at' => 'datetime',
        'lost_at' => 'datetime',
        'tags' => 'array',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(CrmLeadActivity::class);
    }

    public function latestActivities(): HasMany
    {
        return $this->activities()->latest('occurred_at')->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('stage', ['won', 'lost']);
    }

    public function scopeApplyCrmFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';

                    $query->where('full_name', 'like', $like)
                        ->orWhere('company', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('source', 'like', $like)
                        ->orWhere('notes', 'like', $like);
                });
            })
            ->when($filters['stage'] ?? null, fn (Builder $query, string $stage) => $query->where('stage', $stage))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source) => $query->where('source', $source))
            ->when($filters['referral_source'] ?? null, fn (Builder $query, string $source) => $query->where('referral_source', $source))
            ->when($filters['expected_license_type'] ?? null, fn (Builder $query, string $license) => $query->where('expected_license_type', $license))
            ->when($filters['actual_license_type'] ?? null, fn (Builder $query, string $license) => $query->where('actual_license_type', $license))
            ->when($filters['owner_user_id'] ?? null, fn (Builder $query, int $ownerId) => $query->where('owner_user_id', $ownerId))
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['touch_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('last_touch_at', '>=', $date))
            ->when($filters['touch_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('last_touch_at', '<=', $date))
            ->when($filters['follow_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('next_follow_up_at', '>=', $date))
            ->when($filters['follow_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('next_follow_up_at', '<=', $date));
    }

    public function getStageLabelAttribute(): string
    {
        return self::FRENCH_STAGE_LABELS[$this->stage] ?? self::STAGES[$this->stage]['label'] ?? ucfirst((string) $this->stage);
    }

    public function getStageAccentAttribute(): string
    {
        return self::STAGES[$this->stage]['accent'] ?? '#334155';
    }

    public function getTagListAttribute(): array
    {
        return array_values(array_filter($this->tags ?? []));
    }

    public function getTagsAsTextAttribute(): string
    {
        return implode(', ', $this->tag_list);
    }

    public function getArrAttribute(): float
    {
        return (float) ($this->estimated_value ?? 0);
    }

    public function getExpectedLicenseLabelAttribute(): string
    {
        return self::LICENSE_OPTIONS[$this->expected_license_type]['label'] ?? 'Non defini';
    }

    public function getActualLicenseLabelAttribute(): string
    {
        return self::LICENSE_OPTIONS[$this->actual_license_type]['label'] ?? 'Non defini';
    }

    public static function arrForLicense(?string $licenseType): float
    {
        return (float) (self::LICENSE_OPTIONS[$licenseType]['arr'] ?? 0);
    }

    public function isClosed(): bool
    {
        return in_array($this->stage, ['won', 'lost'], true);
    }
}
