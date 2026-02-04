<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DesignTemplate extends Model
{
    protected $fillable = [
        'name',
        'category',
        'format_id',
        'konva_json',
        'preview_path',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function previewUrl(): ?string
    {
        if (!$this->preview_path) return null;
        return asset('storage/' . ltrim($this->preview_path, '/'));
    }
}
