<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_profile_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }
}
