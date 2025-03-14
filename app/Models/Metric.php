<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'goal',
    ];

    public function client()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function entries()
    {
        return $this->hasMany(MetricEntry::class);
    }
	
	public function clientProfile()
{
    return $this->belongsTo(ClientProfile::class, 'client_profile_id');
}

}
