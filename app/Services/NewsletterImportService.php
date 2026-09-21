<?php

namespace App\Services;

use App\Models\Audience;
use App\Models\ClientProfile;
use App\Models\NewsletterContact;
use App\Models\NewsletterImport;
use App\Models\NewsletterOptOut;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewsletterImportService
{
    public function parse(string $contents): array
    {
        $encoding = mb_check_encoding($contents, 'UTF-8') ? 'UTF-8' : 'Windows-1252';
        $contents = mb_convert_encoding($contents, 'UTF-8', $encoding);
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $contents);
        rewind($stream);
        $first = fgets($stream);
        $delimiter = null;
        $headers = [];
        foreach ([";", ",", "\t"] as $candidate) {
            $parsed = array_map(fn ($value) => $this->header($value), str_getcsv((string) $first, $candidate, '"', ''));
            if (in_array('email', $parsed, true)) {
                $delimiter = $candidate;
                $headers = $parsed;
                break;
            }
        }
        if ($delimiter === null || count(array_filter($headers, fn ($header) => $header === 'email')) !== 1) {
            fclose($stream);
            throw ValidationException::withMessages(['csv_file' => 'Une colonne EMAIL unique est obligatoire. Séparateurs acceptés : virgule, point-virgule ou tabulation.']);
        }
        $rows = [];
        $line = 1;
        $seen = [];
        try {
            while (($cells = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
                $line++;
                if (count(array_filter($cells, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }
                if (count($rows) >= 10000) {
                    throw ValidationException::withMessages(['csv_file' => 'Le fichier doit contenir au maximum 10 000 lignes.']);
                }
                $values = count($cells) === count($headers) ? array_combine($headers, $cells) : [];
                $email = NewsletterContact::normalizeEmail($values['email'] ?? '');
                $row = [
                    'line' => $line, 'email' => $email,
                    'first_name' => trim($values['first_name'] ?? ''), 'last_name' => trim($values['last_name'] ?? ''),
                    'opt_in' => trim($values['opt_in'] ?? ''), 'double_opt_in' => trim($values['double_opt_in'] ?? ''),
                    'result' => 'ready',
                ];
                $row['consent'] = $this->confirmed($row['opt_in'], $row['double_opt_in']);
                if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254
                    || mb_strlen($row['first_name']) > 255 || mb_strlen($row['last_name']) > 255) {
                    $row['result'] = 'invalid';
                } elseif (isset($seen[$email])) {
                    $row['result'] = 'duplicate';
                    // Conflicting duplicate rows must not silently grant consent.
                    $rows[$seen[$email]]['consent'] = $rows[$seen[$email]]['consent'] && $row['consent'];
                } else {
                    $seen[$email] = count($rows);
                }
                $rows[] = $row;
            }
        } finally {
            fclose($stream);
        }
        if ($rows === []) {
            throw ValidationException::withMessages(['csv_file' => 'Le fichier ne contient aucun contact.']);
        }

        return ['rows' => $rows, 'encoding' => $encoding, 'delimiter' => $delimiter];
    }

    public function review(array $rows, int $userId): array
    {
        $optOuts = NewsletterOptOut::where('user_id', $userId)->pluck('email')
            ->map(fn ($email) => NewsletterContact::normalizeEmail($email))->flip();
        $existing = NewsletterContact::where('user_id', $userId)->get()->keyBy('email');
        $clients = ClientProfile::where('user_id', $userId)->whereNotNull('email')->pluck('email')
            ->map(fn ($email) => NewsletterContact::normalizeEmail($email))->flip();
        $report = array_fill_keys(['total', 'ready', 'pending', 'existing', 'unsubscribed', 'invalid', 'duplicate', 'client_matches'], 0);
        foreach ($rows as &$row) {
            if (! in_array($row['result'], ['invalid', 'duplicate'], true)) {
                $contact = $existing->get($row['email']);
                $row['result'] = $optOuts->has($row['email']) || $contact?->status === 'unsubscribed'
                    ? 'unsubscribed'
                    : ($contact ? 'existing' : ($row['consent'] ? 'ready' : 'pending'));
                $row['client_match'] = $clients->has($row['email']);
                $report['client_matches'] += (int) $row['client_match'];
            }
            $report['total']++;
            $report[$row['result']]++;
        }
        unset($row);

        return ['rows' => $rows, 'report' => $report];
    }

    public function commit(NewsletterImport $import): NewsletterImport
    {
        return DB::transaction(function () use ($import) {
            // Serialize imports for one therapist; database uniqueness also protects replays.
            User::whereKey($import->user_id)->lockForUpdate()->firstOrFail();
            $import = NewsletterImport::whereKey($import->id)->lockForUpdate()->firstOrFail();
            if ($import->status === 'completed') {
                return $import;
            }
            abort_unless($import->status === 'preview', 422);
            $review = $this->review($import->rows, $import->user_id);
            if ($review['report']['ready'] + $review['report']['pending'] + $review['report']['existing'] === 0) {
                throw ValidationException::withMessages(['csv_file' => 'Aucun contact ne peut être importé. Vérifiez les adresses et les désabonnements.']);
            }
            $audience = Audience::create(['user_id' => $import->user_id, 'name' => $import->audience_name,
                'description' => 'Contacts newsletter importés par l’administration.']);
            $created = 0;
            foreach ($review['rows'] as $row) {
                if (in_array($row['result'], ['invalid', 'duplicate', 'unsubscribed'], true)) {
                    continue;
                }
                $contact = NewsletterContact::firstOrCreate([
                    'user_id' => $import->user_id, 'email' => $row['email'],
                ], [
                    'newsletter_import_id' => $import->id,
                    'first_name' => $row['first_name'] ?: null, 'last_name' => $row['last_name'] ?: null,
                    'status' => $row['consent'] ? 'active' : 'pending',
                ]);
                $created += (int) $contact->wasRecentlyCreated;
                $audience->newsletterContacts()->syncWithoutDetaching([$contact->id]);
            }
            $import->update([
                'audience_id' => $audience->id, 'status' => 'completed', 'committed_at' => now(),
                'rows' => $review['rows'], 'report' => array_merge($import->report, $review['report'], ['created' => $created]),
            ]);

            return $import;
        });
    }

    private function confirmed(string $optIn, string $doubleOptIn): bool
    {
        $values = array_map(fn ($value) => mb_strtolower(trim($value)), [$optIn, $doubleOptIn]);
        // Empty and unknown values never grant consent; an explicit refusal wins.
        foreach ($values as $value) {
            if ($value !== '' && ! in_array($value, ['yes', 'oui', 'true', '1'], true)) {
                return false;
            }
        }

        return count(array_filter($values)) > 0;
    }

    private function header(string $header): string
    {
        $header = str_replace(['-', ' '], '_', Str::lower(Str::ascii(trim($header))));

        return match ($header) {
            'email', 'e_mail', 'email_address', 'adresse_email' => 'email',
            'prenom', 'firstname', 'first_name' => 'first_name',
            'nom', 'lastname', 'last_name' => 'last_name',
            'opt_in', 'double_opt_in' => $header,
            default => $header,
        };
    }
}
