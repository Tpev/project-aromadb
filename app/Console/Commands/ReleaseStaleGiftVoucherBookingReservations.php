<?php

namespace App\Console\Commands;

use App\Services\GiftVoucherRedeemService;
use Illuminate\Console\Command;

class ReleaseStaleGiftVoucherBookingReservations extends Command
{
    protected $signature = 'gift-vouchers:release-stale-booking-reservations';

    protected $description = 'Release expired online booking gift voucher reservations that never reached payment.';

    public function handle(GiftVoucherRedeemService $service): int
    {
        $released = $service->releaseStaleOnlineReservations();

        $this->info("Released {$released} stale gift voucher booking reservation(s).");

        return self::SUCCESS;
    }
}
