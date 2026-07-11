<?php

namespace App\Domain\OfferJourneys\Services;

class OfferJourneyMediaUrl
{
    public function embed(?string $url): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $id = $query['v'] ?? null;
            return $this->safeId($id) ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
        }
        if ($host === 'youtu.be') {
            $id = trim((string) ($parts['path'] ?? ''), '/');
            return $this->safeId($id) ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
        }
        if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)) {
            $id = trim((string) ($parts['path'] ?? ''), '/');
            return ctype_digit($id) ? 'https://player.vimeo.com/video/'.$id : null;
        }

        return null;
    }

    private function safeId(?string $id): bool
    {
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id) === 1;
    }
}
