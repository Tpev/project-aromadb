<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyFormField extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'options_json' => 'array',
        'is_required' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyForm::class, 'offer_journey_form_id');
    }
}
