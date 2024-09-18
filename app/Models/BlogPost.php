<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = ['REF', 'Title', 'slug', 'Tags', 'Contents', 'RelatedPostsREF', 'MetaDescription'];

    // Slug generation for the blog post title
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blogPost) {
            $blogPost->slug = Str::slug($blogPost->Title);
        });
    }
}
