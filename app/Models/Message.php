<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'client_profile_id',
        'user_id',
        'sender_type',
        'content',
    ];

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
