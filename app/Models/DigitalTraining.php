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
        'is_free',
        'price_cents',
        'tax_rate',
        'access_type',
        'status',
        'estimated_duration_minutes',
		'product_id',  
    ];

    protected $casts = [
        'tags'      => 'array',
        'is_free'   => 'boolean',
        'price_cents' => 'integer',
        'tax_rate'  => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modules()
    {
        return $this->hasMany(TrainingModule::class)
            ->orderBy('display_order');
    }

    public function getFormattedPriceAttribute(): ?string
    {
        if ($this->is_free || is_null($this->price_cents)) {
            return null;
        }

        return number_format($this->price_cents / 100, 2, ',', ' ') . ' €';
    }
	// app/Models/DigitalTraining.php

public function enrollments()
{
    return $this->hasMany(DigitalTrainingEnrollment::class);
}

}
