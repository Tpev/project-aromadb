<?php
// app/Models/Response.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = ['questionnaire_id', 'client_profile_id', 'token', 'answers', 'is_completed'];

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
	
}
