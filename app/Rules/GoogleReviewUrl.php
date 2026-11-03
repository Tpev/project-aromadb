<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GoogleReviewUrl implements ValidationRule
{
    public const HOSTS = ['g.page', 'search.google.com', 'google.com', 'www.google.com', 'maps.app.goo.gl', 'goo.gl'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->isValid($value)) {
            $fail('Collez le lien de demande d’avis fourni par Google (par exemple https://g.page/r/…/review), puis vérifiez-le avec « Tester le lien ».');
        }
    }

    private function isValid(string $value): bool
    {
        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }

        $url = parse_url($value);
        $host = strtolower($url['host'] ?? '');
        $path = $url['path'] ?? '';

        if (strtolower($url['scheme'] ?? '') !== 'https'
            || ! in_array($host, self::HOSTS, true)
            || isset($url['user']) || isset($url['pass']) || isset($url['port'])
            || isset($url['fragment'])) {
            return false;
        }

        if ($host === 'g.page') {
            return (bool) preg_match('~^/(?:r/)?[a-zA-Z0-9_-]+/review/?$~', $path);
        }

        // Short links cannot be resolved without a network request.
        // The UI asks the therapist to verify the destination.
        if ($host === 'maps.app.goo.gl') {
            return (bool) preg_match('~^/[a-zA-Z0-9]+/?$~', $path);
        }

        if ($host === 'goo.gl') {
            return (bool) preg_match('~^/maps/[a-zA-Z0-9]+/?$~', $path);
        }

        parse_str($url['query'] ?? '', $query);

        return rtrim($path, '/') === '/local/writereview'
            && is_string($query['placeid'] ?? null)
            && preg_match('/^[a-zA-Z0-9_-]+$/', $query['placeid']) === 1;
    }
}
