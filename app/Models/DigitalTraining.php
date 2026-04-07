<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'cover_image_path',
        'tags',

        // Pricing
        'is_free',
        'price_cents',
        'tax_rate',
        'installments_enabled',
        'allowed_installments',

        // Publishing / access
        'access_type',
        'status',

        'estimated_duration_minutes',

        // Optional link to a product
        'product_id',
        'use_global_retractation_notice',
    ];

    protected $casts = [
        'tags'        => 'array',
        'is_free'     => 'boolean',
        'price_cents' => 'integer',
        'tax_rate'    => 'float',
        'installments_enabled' => 'boolean',
        'allowed_installments' => 'array',
        'use_global_retractation_notice' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function modules()
    {
        return $this->hasMany(TrainingModule::class)
            ->orderBy('display_order');
    }

    public function enrollments()
    {
        return $this->hasMany(DigitalTrainingEnrollment::class);
    }

    public function getFormattedPriceAttribute(): ?string
    {
        if (($this->is_free ?? false) || is_null($this->price_cents)) {
            return null;
        }

        return number_format($this->price_cents / 100, 2, ',', ' ') . ' €';
    }

    public function requiresRetractationNotice(): bool
    {
        if (!($this->use_global_retractation_notice ?? false)) {
            return false;
        }

        $owner = $this->relationLoaded('user') ? $this->user : $this->user()->first();

        return $owner?->hasDigitalSalesRetractationNoticeConfigured() ?? false;
    }
}
