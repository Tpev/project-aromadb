<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InventoryItem extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'reference',
        'description',
        'price',
        'selling_price',
        'quantity_in_stock',
        'brand',
    ];

    // Define the relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
