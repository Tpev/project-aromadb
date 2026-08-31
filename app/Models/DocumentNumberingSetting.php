<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberingSetting extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'enabled',
        'format',
        'reset_frequency',
        'version',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'version' => 'integer',
    ];
}
