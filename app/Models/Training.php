<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
    ];

    public function chapters()
    {
        // A Training has many Chapters
        return $this->hasMany(Chapter::class);
    }
}
