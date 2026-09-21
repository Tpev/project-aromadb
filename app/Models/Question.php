<?php
// app/Models/Question.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['questionnaire_id', 'text', 'type', 'allow_multiple'];

    protected $casts = ['allow_multiple' => 'boolean'];

    public function allowsMultipleAnswers(): bool
    {
        return $this->type === 'multiple_choice' && (bool) ($this->allow_multiple ?? false);
    }

    public function choiceOptions(): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', explode(',', (string) $this->options)),
            fn (string $option) => $option !== ''
        )));
    }

    /**
     * Get the questionnaire that owns the question.
     */
    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }
}
