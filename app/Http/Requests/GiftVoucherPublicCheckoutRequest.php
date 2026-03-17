<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GiftVoucherPublicCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_eur' => ['required', 'numeric', 'min:5', 'max:5000'],
            'buyer_name' => ['required', 'string', 'max:120'],
            'buyer_email' => ['required', 'email', 'max:190'],
            'buyer_phone' => ['nullable', 'string', 'max:40'],
            'recipient_name' => ['nullable', 'string', 'max:120'],
            'recipient_email' => ['nullable', 'email', 'max:190'],
            'message' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}

