<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;

class OfferJourneySavedFilter extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['filters_json' => 'array'];
}
