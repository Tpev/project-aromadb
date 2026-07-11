<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;

class OfferJourneyContactImport extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rows_json' => 'array',
        'report_json' => 'array',
        'created_contact_ids_json' => 'array',
        'committed_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];
}
