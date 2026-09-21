<?php
// app/Models/Response.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id',
        'client_profile_id',
        'appointment_id',
        'token',
        'answers',
        'is_completed',
        'source',
    ];

    protected $casts = [
        'answers' => 'array',
        'is_completed' => 'boolean',
    ];

    public function decodedAnswers(): array
    {
        // Older controllers JSON-encoded answers before the Eloquent array cast.
        $answers = $this->answers;
        if (is_string($answers)) {
            $answers = json_decode($answers, true);
        }

        return is_array($answers) ? $answers : [];
    }

    /**
     * Get the questionnaire that the response belongs to.
     */
    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * Get the client profile that the response belongs to.
     */
    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
	
}
