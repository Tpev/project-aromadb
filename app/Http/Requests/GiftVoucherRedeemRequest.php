<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GiftVoucherRedeemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount_eur' => ['required', 'numeric', 'min:0.01', 'max:5000'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
