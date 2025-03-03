<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'title',
        'position',
    ];

    public function training()
    {
        // A Chapter belongs to a single Training
        return $this->belongsTo(Training::class);
    }

    public function lessons()
    {
        // A Chapter has many Lessons
        return $this->hasMany(Lesson::class);
    }
}

