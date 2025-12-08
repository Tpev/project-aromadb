<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'digital_training_id',
        'title',
        'description',
        'display_order',
    ];

    public function training()
    {
        return $this->belongsTo(DigitalTraining::class, 'digital_training_id');
    }

    public function blocks()
    {
        return $this->hasMany(TrainingBlock::class)
            ->orderBy('display_order');
    }
}
