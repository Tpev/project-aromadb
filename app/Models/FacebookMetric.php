<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookMetric extends Model
{
    protected $fillable = ['fan_count', 'followers_count', 'page_id'];
}
