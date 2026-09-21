<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterContact extends Model
{
    protected $fillable = ['user_id', 'newsletter_import_id', 'email', 'first_name', 'last_name', 'status'];

    public static function normalizeEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }
}
