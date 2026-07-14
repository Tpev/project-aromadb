<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceActivityLog extends Model
{
    protected $fillable = [
        'invoice_id',
        'user_id',
        'event',
        'message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
