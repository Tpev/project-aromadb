<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TherapistArticle extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content_html',
        'content_json',
        'meta_description',
        'cover_path',
        'status',
        'published_at',
        'tags',
        'reading_time',
        'views',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public static function makeUniqueSlugForUser(int $userId, string $seed, ?int $ignoreId = null): string
    {
        $base = Str::slug($seed);
        $slug = $base ?: 'article';

        $i = 2;
        while (static::query()
            ->where('user_id', $userId)
            ->where('slug', $slug)
            ->when($ignoreId, fn($qq) => $qq->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = ($base ?: 'article') . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public static function estimateReadingTime(string $html): int
    {
        $text = trim(strip_tags($html));
        if ($text === '') return 1;

        $words = str_word_count($text);
        $minutes = (int) ceil($words / 200);

        return max(1, $minutes);
    }
}
