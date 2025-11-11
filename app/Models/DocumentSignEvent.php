<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSignEvent extends Model
{
    protected $fillable = [
        'document_id','role','signed_at','signer_ip','signer_user_agent','signature_image_path'
    ];
    protected $casts = ['signed_at'=>'datetime'];

    public function document(){ return $this->belongsTo(Document::class); }
}
