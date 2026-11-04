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

    protected function prepareForValidation(): void
    {
        // Also support older forms and browsers where the preview script did not run.
        if ($this->hasFile('gift_voucher_background') && ! $this->boolean('remove_gift_voucher_background')) {
            $this->merge(['gift_voucher_background_mode' => 'custom_upload']);
        }
    }

    public function messages(): array
    {
        return [
            'gift_voucher_background.mimes' => 'Choisissez une image JPG, PNG ou WebP.',
            'gift_voucher_background.max' => 'L’image ne doit pas dépasser 6 Mo.',
            'gift_voucher_background.uploaded' => 'L’image n’a pas pu être envoyée. Choisissez un fichier de moins de 6 Mo et réessayez.',
            'gift_voucher_validity_months.integer' => 'La durée de validité doit être un nombre entier de mois.',
            'gift_voucher_validity_months.min' => 'La durée de validité doit être comprise entre 1 et 120 mois.',
            'gift_voucher_validity_months.max' => 'La durée de validité doit être comprise entre 1 et 120 mois.',
        ];
    }
}
