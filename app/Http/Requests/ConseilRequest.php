<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConseilRequest extends FormRequest
{
    public const MAX_LINKS = 20;

    public function authorize(): bool
    {
        $conseil = $this->route('conseil');

        return $this->user() && (! $conseil || $conseil->user_id === $this->user()->id);
    }

    protected function prepareForValidation(): void
    {
        // Older forms omit both fields: leave saved links untouched.
        if (! $this->exists('links') && ! $this->boolean('links_present')) {
            return;
        }

        $links = $this->input('links', []);

        if (is_array($links)) {
            // Ignore unused rows, but keep malformed rows for validation.
            $links = array_values(array_filter($links, function ($link) {
                return ! is_array($link)
                    || array_diff(array_keys($link), ['label', 'url'])
                    || ! in_array($link['label'] ?? null, [null, ''], true)
                    || ! in_array($link['url'] ?? null, [null, ''], true);
            }));
        }

        $this->merge(['links' => $links]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:4048',
            'attachment' => 'nullable|file|mimes:pdf|max:10048',
            'tag' => 'nullable|string|max:255',
            'links' => ['sometimes', 'nullable', 'array', 'max:'.self::MAX_LINKS],
            'links.*' => ['required', 'array:label,url'],
            'links.*.label' => ['nullable', 'string', 'max:255'],
            'links.*.url' => ['required', 'string', 'max:2048', 'url:http,https'],
        ];
    }

    public function messages(): array
    {
        return [
            'links.array' => 'Les liens doivent être ajoutés avec le bouton « Ajouter un lien ».',
            'links.max' => 'Vous pouvez ajouter jusqu’à '.self::MAX_LINKS.' liens par conseil.',
            'links.*.required' => 'Vérifiez les informations de chaque lien.',
            'links.*.array' => 'Vérifiez les informations de chaque lien.',
            'links.*.label.string' => 'Le titre du lien doit être du texte.',
            'links.*.label.max' => 'Le titre du lien ne doit pas dépasser 255 caractères.',
            'links.*.url.required' => 'Indiquez l’adresse du lien ou supprimez cette ligne.',
            'links.*.url.string' => 'Indiquez une adresse web valide.',
            'links.*.url.max' => 'L’adresse du lien ne doit pas dépasser 2 048 caractères.',
            'links.*.url.url' => 'Indiquez une adresse web complète commençant par https:// ou http://.',
        ];
    }
}
