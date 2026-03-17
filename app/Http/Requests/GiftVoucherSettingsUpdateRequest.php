<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GiftVoucherSettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'gift_voucher_online_enabled' => ['nullable', 'boolean'],
            'gift_voucher_background_mode' => ['required', 'in:default,custom_upload'],
            'gift_voucher_background' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:6144'],
            'remove_gift_voucher_background' => ['nullable', 'boolean'],
        ];
    }
}

