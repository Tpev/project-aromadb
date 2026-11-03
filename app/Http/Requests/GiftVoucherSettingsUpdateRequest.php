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
            'gift_voucher_validity_months' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:120'],
            'gift_voucher_background_mode' => ['required', 'in:default,custom_upload'],
            'gift_voucher_background' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:6144'],
            'remove_gift_voucher_background' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'gift_voucher_validity_months.integer' => 'La durée de validité doit être un nombre entier de mois.',
            'gift_voucher_validity_months.min' => 'La durée de validité doit être comprise entre 1 et 120 mois.',
            'gift_voucher_validity_months.max' => 'La durée de validité doit être comprise entre 1 et 120 mois.',
        ];
    }
}

