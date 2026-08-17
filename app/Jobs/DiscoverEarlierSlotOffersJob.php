<?php

namespace App\Jobs;

use App\Services\AppointmentEarlierSlotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DiscoverEarlierSlotOffersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(
        public int $releasedAppointmentId,
        public int $userId,
        public int $productId,
        public string $slotStart,
        public int $duration,
        public string $mode,
        public ?int $practiceLocationId,
        public ?string $locationFingerprint = null,
    ) {
        $this->onQueue('default');
    }

    public function backoff(): array
    {
        return [30, 120, 300, 900];
    }

    public function handle(AppointmentEarlierSlotService $service): void
    {
        $service->discover(
            $this->releasedAppointmentId,
            $this->userId,
            $this->productId,
            $this->slotStart,
            $this->duration,
            $this->mode,
            $this->practiceLocationId,
            $this->locationFingerprint,
        );
    }
}
