<?php

namespace App\Support;

final readonly class AccountDataExportResult
{
    public function __construct(
        public int $userId,
        public array $datasetCounts,
        public int $exportedFileCount,
        public array $warnings,
        public ?string $relativePath = null,
        public ?string $absolutePath = null,
        public ?int $sizeBytes = null,
    ) {}

    public function totalRows(): int
    {
        return array_sum($this->datasetCounts);
    }
}
