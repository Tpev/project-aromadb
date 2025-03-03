<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'content',
        'position',
    ];

    public function chapter()
    {
        // A Lesson belongs to a single Chapter
        return $this->belongsTo(Chapter::class);
    }
}
