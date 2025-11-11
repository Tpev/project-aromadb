<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'owner_user_id','client_profile_id','appointment_id',
        'original_name','storage_path','pages','status',
        'uploaded_by_user_id','final_pdf_path','hash_original','hash_final'
    ];

    public function owner()          { return $this->belongsTo(User::class,'owner_user_id'); }
    public function clientProfile()  { return $this->belongsTo(ClientProfile::class); }
    public function appointment()    { return $this->belongsTo(Appointment::class); }

    public function signing()        { return $this->hasOne(DocumentSigning::class)->latestOfMany(); }
    public function signings()       { return $this->hasMany(DocumentSigning::class); }
    public function signEvents()     { return $this->hasMany(DocumentSignEvent::class); }

    public function isComplete(): bool { return $this->status === 'signed'; }
}
