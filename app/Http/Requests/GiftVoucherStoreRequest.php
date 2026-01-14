<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GiftVoucherStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // therapist auth middleware will protect route; keep simple
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount_eur' => ['required', 'numeric', 'min:5', 'max:5000'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],

            'buyer_name' => ['nullable', 'string', 'max:120'],
            'buyer_email' => ['required', 'email', 'max:190'],

            'recipient_name' => ['nullable', 'string', 'max:120'],
            'recipient_email' => ['nullable', 'email', 'max:190'],

            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount_eur.required' => 'Veuillez saisir un montant.',
            'amount_eur.min' => 'Le montant minimum est 5 €.',
            'buyer_email.required' => 'Veuillez saisir l’email de l’acheteur.',
            'expires_at.after_or_equal' => 'La date d’expiration doit être aujourd’hui ou une date future.',
        ];
    }
}
