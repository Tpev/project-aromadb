<?php

namespace App\Domain\OfferJourneys\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OfferJourneyDnsDiagnostic
{
    public function check(bool $fresh = false): array
    {
        $domain = Str::lower((string) config('offer_journeys.deliverability.domain', 'olithea.fr'));
        $key = 'offer-journeys:dns-diagnostic:'.hash('sha256', $domain);
        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addMinutes(15), function () use ($domain): array {
            $spfValues = $this->txt($domain);
            $spf = collect($spfValues)->first(fn (string $value): bool => Str::startsWith(Str::lower($value), 'v=spf1'));
            $dmarc = collect($this->txt('_dmarc.'.$domain))
                ->first(fn (string $value): bool => Str::startsWith(Str::lower($value), 'v=dmarc1'));
            $selectors = config('offer_journeys.deliverability.dkim_selectors', []);
            $dkim = collect($selectors)->mapWithKeys(function (string $selector) use ($domain): array {
                $records = $this->txt($selector.'._domainkey.'.$domain);
                $valid = collect($records)->contains(fn (string $value): bool => Str::contains(Str::lower($value), ['v=dkim1', 'p=']));

                return [$selector => ['valid' => $valid, 'records' => $records]];
            })->all();

            return [
                'domain' => $domain,
                'checked_at' => now(),
                'spf' => [
                    'valid' => filled($spf) && Str::contains(Str::lower($spf), ['amazonses.com', 'include:']),
                    'value' => $spf,
                    'recommendation' => filled($spf)
                        ? 'Verifier que le mecanisme Amazon SES figure dans cet enregistrement.'
                        : 'Publier un unique enregistrement TXT SPF autorisant Amazon SES.',
                ],
                'dkim' => [
                    'valid' => $dkim !== [] && collect($dkim)->every(fn (array $item): bool => $item['valid']),
                    'selectors' => $dkim,
                    'recommendation' => $dkim === []
                        ? 'Renseigner les selecteurs DKIM fournis par Amazon SES.'
                        : 'Publier les CNAME ou TXT manquants fournis par Amazon SES.',
                ],
                'dmarc' => [
                    'valid' => filled($dmarc),
                    'value' => $dmarc,
                    'enforcement' => $dmarc && ! Str::contains(Str::lower($dmarc), 'p=none'),
                    'recommendation' => filled($dmarc)
                        ? 'Surveiller les rapports puis renforcer progressivement la politique si elle est a p=none.'
                        : 'Publier un enregistrement TXT DMARC avec une adresse de rapports controlee.',
                ],
            ];
        });
    }

    protected function txt(string $name): array
    {
        $records = @dns_get_record($name, DNS_TXT) ?: [];

        return collect($records)
            ->map(fn (array $record): string => (string) ($record['txt'] ?? implode('', $record['entries'] ?? [])))
            ->filter()
            ->values()
            ->all();
    }
}
