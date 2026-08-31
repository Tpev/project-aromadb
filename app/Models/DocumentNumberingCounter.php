<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberingCounter extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'version',
        'period_key',
        'next_sequence',
    ];

    protected $casts = [
        'version' => 'integer',
        'next_sequence' => 'integer',
    ];
}
