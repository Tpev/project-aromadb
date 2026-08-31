<?php

namespace App\Services;

use App\Models\DocumentNumberingChangeLog;
use App\Models\DocumentNumberingCounter;
use App\Models\DocumentNumberingSetting;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentNumberingService
{
    public const INVOICE = 'invoice';

    public const QUOTE = 'quote';

    public function __construct(private readonly DocumentNumberFormatter $formatter) {}

    public function configuration(User $user, string $documentType, ?CarbonInterface $date = null): array
    {
        $this->assertDocumentType($documentType);
        $date ??= now();
        $setting = DocumentNumberingSetting::query()
            ->where('user_id', $user->id)
            ->where('document_type', $documentType)
            ->first();

        $format = $setting?->format ?: $this->defaultFormat($documentType);
        $reset = $setting?->reset_frequency ?: 'never';
        $nextSequence = 1;

        if ($setting?->enabled) {
            $period = $this->formatter->periodKey($reset, $date);
            $nextSequence = (int) (DocumentNumberingCounter::query()
                ->where('user_id', $user->id)
                ->where('document_type', $documentType)
                ->where('version', $setting->version)
                ->where('period_key', $period)
                ->value('next_sequence') ?: 1);
        }

        return [
            'enabled' => (bool) ($setting?->enabled ?? false),
            'format' => $format,
            'reset_frequency' => $reset,
            'next_sequence' => $nextSequence,
            'version' => (int) ($setting?->version ?? 0),
            'preview' => $this->formatter->format($format, $date, $nextSequence),
        ];
    }

    public function requiresConfirmation(User $user, string $documentType, array $configuration): bool
    {
        $current = $this->configuration($user, $documentType);
        $normalized = $this->normalizeConfiguration($documentType, $configuration);

        return $this->configurationChanged($current, $normalized);
    }

    public function updateConfiguration(
        User $user,
        string $documentType,
        array $configuration,
        ?User $actor = null
    ): array {
        $normalized = $this->normalizeConfiguration($documentType, $configuration);

        return DB::transaction(function () use ($user, $documentType, $normalized, $actor): array {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $setting = DocumentNumberingSetting::query()
                ->where('user_id', $user->id)
                ->where('document_type', $documentType)
                ->lockForUpdate()
                ->first();
            $current = $this->configuration($user, $documentType);

            if (! $this->configurationChanged($current, $normalized)) {
                return $current;
            }

            if (! $normalized['enabled'] && ! $setting) {
                return $current;
            }

            $before = $setting ? $current : null;
            $version = ((int) ($setting?->version ?? 0)) + 1;

            $setting ??= new DocumentNumberingSetting([
                'user_id' => $user->id,
                'document_type' => $documentType,
            ]);
            $setting->fill([
                'enabled' => $normalized['enabled'],
                'format' => $normalized['format'],
                'reset_frequency' => $normalized['reset_frequency'],
                'version' => $version,
            ])->save();

            if ($normalized['enabled']) {
                $period = $this->formatter->periodKey($normalized['reset_frequency'], now());
                $preview = $this->formatter->format(
                    $normalized['format'],
                    now(),
                    $normalized['next_sequence']
                );

                if ($this->customNumberExists($user->id, $documentType, $preview)) {
                    throw ValidationException::withMessages([
                        $documentType.'_numbering_next_sequence' => 'Ce prochain numéro existe déjà. Choisissez une autre séquence.',
                    ]);
                }

                DocumentNumberingCounter::create([
                    'user_id' => $user->id,
                    'document_type' => $documentType,
                    'version' => $version,
                    'period_key' => $period,
                    'next_sequence' => $normalized['next_sequence'],
                ]);
            }

            $after = $this->configuration($user, $documentType);
            DocumentNumberingChangeLog::create([
                'user_id' => $user->id,
                'actor_user_id' => $actor?->id,
                'document_type' => $documentType,
                'before_configuration' => $before,
                'after_configuration' => $after,
            ]);

            return $after;
        }, 3);
    }

    public function allocateInvoice(User|int $user, CarbonInterface|string $date): array
    {
        return $this->allocate($user, self::INVOICE, $date);
    }

    public function allocateQuote(User|int $user, CarbonInterface|string $date): array
    {
        return $this->allocate($user, self::QUOTE, $date);
    }

    private function allocate(User|int $user, string $documentType, CarbonInterface|string $date): array
    {
        $userId = $user instanceof User ? $user->id : $user;
        $documentDate = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        return DB::transaction(function () use ($userId, $documentType, $documentDate): array {
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

            $setting = DocumentNumberingSetting::query()
                ->where('user_id', $userId)
                ->where('document_type', $documentType)
                ->lockForUpdate()
                ->first();

            $internalInvoiceNumber = $documentType === self::INVOICE
                ? $this->nextInternalInvoiceNumber($userId)
                : null;

            if (! $setting?->enabled) {
                return [
                    'invoice_number' => $internalInvoiceNumber,
                    'quote_number' => $documentType === self::QUOTE ? $this->nextLegacyQuoteNumber($userId) : null,
                    'custom_number' => null,
                    'numbering_family' => null,
                    'number_sequence' => null,
                    'number_period' => null,
                    'numbering_version' => null,
                ];
            }

            $format = $this->formatter->validate((string) $setting->format);
            $period = $this->formatter->periodKey($setting->reset_frequency, $documentDate);
            $counter = DocumentNumberingCounter::query()
                ->where('user_id', $userId)
                ->where('document_type', $documentType)
                ->where('version', $setting->version)
                ->where('period_key', $period)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = DocumentNumberingCounter::create([
                    'user_id' => $userId,
                    'document_type' => $documentType,
                    'version' => $setting->version,
                    'period_key' => $period,
                    'next_sequence' => 1,
                ]);
            }

            $sequence = (int) $counter->next_sequence;
            $customNumber = $this->formatter->format($format, $documentDate, $sequence);

            if ($this->customNumberExists($userId, $documentType, $customNumber)) {
                throw ValidationException::withMessages([
                    'numbering' => "Le numéro {$customNumber} existe déjà. Corrigez la prochaine séquence dans Informations de l’entreprise.",
                ]);
            }

            $counter->increment('next_sequence');

            return [
                'invoice_number' => $internalInvoiceNumber,
                'quote_number' => $documentType === self::QUOTE ? $customNumber : null,
                'custom_number' => $customNumber,
                'numbering_family' => $documentType,
                'number_sequence' => $sequence,
                'number_period' => $period,
                'numbering_version' => (int) $setting->version,
            ];
        }, 3);
    }

    private function normalizeConfiguration(string $documentType, array $configuration): array
    {
        $this->assertDocumentType($documentType);
        $enabled = (bool) ($configuration['enabled'] ?? false);
        $rawFormat = trim((string) ($configuration['format'] ?? ''));
        $rawFormat = $rawFormat !== '' ? $rawFormat : $this->defaultFormat($documentType);
        try {
            $format = $this->formatter->validate($rawFormat);
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages([
                $documentType.'_numbering_format' => $exception->errors()['numbering_format'] ?? ['Le format est invalide.'],
            ]);
        }
        $reset = (string) ($configuration['reset_frequency'] ?? 'never');

        if (! in_array($reset, DocumentNumberFormatter::RESET_FREQUENCIES, true)) {
            throw ValidationException::withMessages([
                $documentType.'_numbering_reset_frequency' => 'La périodicité de remise à zéro est invalide.',
            ]);
        }

        $hasYear = str_contains($format, '{YYYY}') || str_contains($format, '{YY}');
        if ($reset === 'yearly' && ! $hasYear) {
            throw ValidationException::withMessages([
                $documentType.'_numbering_format' => 'Ajoutez {YYYY} ou {YY} au format pour une remise à zéro annuelle.',
            ]);
        }

        if ($reset === 'monthly' && (! $hasYear || ! str_contains($format, '{MM}'))) {
            throw ValidationException::withMessages([
                $documentType.'_numbering_format' => 'Ajoutez l’année ({YYYY} ou {YY}) et {MM} au format pour une remise à zéro mensuelle.',
            ]);
        }

        $nextSequence = (int) ($configuration['next_sequence'] ?? 1);
        if ($nextSequence < 1 || $nextSequence > 999999999999999999) {
            throw ValidationException::withMessages([
                $documentType.'_numbering_next_sequence' => 'La prochaine séquence doit être comprise entre 1 et 999 999 999 999 999 999.',
            ]);
        }

        return [
            'enabled' => $enabled,
            'format' => $format,
            'reset_frequency' => $reset,
            'next_sequence' => $nextSequence,
        ];
    }

    private function configurationChanged(array $current, array $next): bool
    {
        return (bool) $current['enabled'] !== (bool) $next['enabled']
            || (string) $current['format'] !== (string) $next['format']
            || (string) $current['reset_frequency'] !== (string) $next['reset_frequency']
            || ((bool) $next['enabled'] && (int) $current['next_sequence'] !== (int) $next['next_sequence']);
    }

    private function nextInternalInvoiceNumber(int $userId): int
    {
        $max = DB::table('invoices')
            ->where('user_id', $userId)
            ->whereIn('type', ['invoice', 'credit_note'])
            ->whereNotNull('invoice_number')
            ->max('invoice_number');

        return ((int) $max) + 1;
    }

    private function nextLegacyQuoteNumber(int $userId): string
    {
        $lastQuoteId = (int) (DB::table('invoices')
            ->where('user_id', $userId)
            ->where('type', 'quote')
            ->orderByDesc('id')
            ->value('id') ?: 0);

        return 'D-'.str_pad((string) ($lastQuoteId + 1), 5, '0', STR_PAD_LEFT);
    }

    private function customNumberExists(int $userId, string $documentType, string $number): bool
    {
        return Invoice::query()
            ->where('user_id', $userId)
            ->where('numbering_family', $documentType)
            ->where('custom_number', $number)
            ->exists();
    }

    private function defaultFormat(string $documentType): string
    {
        return $documentType === self::QUOTE
            ? DocumentNumberFormatter::DEFAULT_QUOTE_FORMAT
            : DocumentNumberFormatter::DEFAULT_INVOICE_FORMAT;
    }

    private function assertDocumentType(string $documentType): void
    {
        if (! in_array($documentType, [self::INVOICE, self::QUOTE], true)) {
            throw new \InvalidArgumentException('Type de document de numérotation invalide.');
        }
    }
}
