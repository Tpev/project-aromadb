<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Emargement extends Model
{
    use HasFactory;


protected $fillable = [
  'appointment_id','therapist_id','client_email','token','expires_at','status',
  'signed_at','signer_ip','signer_user_agent','signature_image_path','pdf_path','meta'
];


    protected $casts = [
        'expires_at' => 'datetime',
        'signed_at'  => 'datetime',
        'meta'       => 'array',
    ];

    public function appointment() { return $this->belongsTo(Appointment::class); }
    public function therapist()   { return $this->belongsTo(User::class, 'therapist_id'); }

    public function isExpired(): bool { return now()->greaterThan($this->expires_at); }
    public function canSign(): bool   { return $this->status === 'pending' && !$this->isExpired(); }
}
