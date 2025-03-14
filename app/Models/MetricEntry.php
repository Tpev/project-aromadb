<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_id',
        'entry_date',
        'value',
    ];

    public function metric()
    {
        return $this->belongsTo(Metric::class);
    }
}
