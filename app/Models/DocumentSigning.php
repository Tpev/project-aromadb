<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DocumentSigning extends Model
{
    protected $fillable = ['document_id','token','current_role','status','expires_at'];
    protected $casts = ['expires_at'=>'datetime'];

    public function document(){ return $this->belongsTo(Document::class); }
    public function isExpired(): bool { return $this->expires_at && $this->expires_at->isPast(); }
}
