<?php
// app/Models/Question.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['questionnaire_id', 'text', 'type'];

    /**
     * Get the questionnaire that owns the question.
     */
    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }
}
