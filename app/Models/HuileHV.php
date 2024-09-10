<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Favorite;
use Illuminate\Support\Str;

class HuileHV extends Model
{
    use HasFactory;

    protected $table = 'huile_hvs';

    protected $fillable = [
        'REF', 
        'NomHV',
        'slug',  // Ensure slug is fillable
        'NomLatin',
        'Provenance',
        'OrganeProducteur',
        'Sb',
        'Properties',
        'Indications',
        'ContreIndications',
        'Note',
        'Description',
    ];

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    // Automatically generate the slug on creation or update
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($huileHV) {
            // If no slug is present, generate it from the name
            $huileHV->slug = Str::slug($huileHV->NomHV);
        });

        static::updating(function ($huileHV) {
            // Ensure slug is updated if NomHV changes
            if ($huileHV->isDirty('NomHV')) {
                $huileHV->slug = Str::slug($huileHV->NomHV);
            }
        });
    }
}
