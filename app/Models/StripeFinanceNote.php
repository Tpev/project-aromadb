<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeFinanceNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_finance_customer_id',
        'stripe_finance_subscription_id',
        'created_by_user_id',
        'type',
        'body',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(StripeFinanceCustomer::class, 'stripe_finance_customer_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(StripeFinanceSubscription::class, 'stripe_finance_subscription_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
