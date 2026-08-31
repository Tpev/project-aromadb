<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberingChangeLog extends Model
{
    protected $fillable = [
        'user_id',
        'actor_user_id',
        'document_type',
        'before_configuration',
        'after_configuration',
    ];

    protected $casts = [
        'before_configuration' => 'array',
        'after_configuration' => 'array',
    ];
}
