<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinanceForecastAssumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'conservative_new_customers',
        'optimistic_new_customers',
    ];

    protected $casts = [
        'month' => 'date',
        'conservative_new_customers' => 'integer',
        'optimistic_new_customers' => 'integer',
    ];
}
