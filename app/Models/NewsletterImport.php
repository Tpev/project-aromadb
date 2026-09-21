<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterImport extends Model
{
    protected $fillable = ['user_id', 'created_by_user_id', 'audience_id', 'audience_name', 'original_filename', 'file_hash', 'status', 'rows', 'report', 'committed_at'];

    protected $casts = ['rows' => 'array', 'report' => 'array', 'committed_at' => 'datetime'];

    public function audience()
    {
        return $this->belongsTo(Audience::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
