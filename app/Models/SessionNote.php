<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_profile_id',
        'user_id',
        'session_note_template_id',
        'note',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function template()
    {
        return $this->belongsTo(SessionNoteTemplate::class, 'session_note_template_id');
    }
}
