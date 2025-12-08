<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_module_id',
        'type',
        'title',
        'content',
        'file_path',
        'meta',
        'display_order',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function module()
    {
        return $this->belongsTo(TrainingModule::class, 'training_module_id');
    }
}
