<?php

namespace App\Console\Commands;

use App\Domain\OfferJourneys\Services\OfferJourneyConversionAttributor;
use App\Models\Appointment;
use App\Models\DigitalTrainingEnrollment;
use App\Models\GiftVoucherOrder;
use App\Models\Reservation;
use Illuminate\Console\Command;

class ReconcileOfferJourneyConversions extends Command
{
    protected $signature = 'offer-journeys:reconcile-conversions {--days=35 : Fenetre de recherche} {--dry-run : Compter sans attribuer}';

    protected $description = 'Rejoue de facon idempotente l attribution des conversions des parcours d offre';

    public function handle(OfferJourneyConversionAttributor $attributor): int
    {
        if (! config('offer_journeys.enabled')) {
            $this->info('Parcours d offre desactive: aucune action.');
            return self::SUCCESS;
        }

        $since = now()->subDays(max(1, min(365, (int) $this->option('days'))));
        $sources = [
            [Appointment::class, 'appointment'],
            [Reservation::class, 'reservation'],
            [DigitalTrainingEnrollment::class, 'training'],
            [GiftVoucherOrder::class, 'giftVoucher'],
        ];
        $count = 0;

        foreach ($sources as [$model, $method]) {
            $query = $model::query()->where('updated_at', '>=', $since);
            if ($this->option('dry-run')) {
                $count += $query->count();
                continue;
            }
            $query->chunkById(100, function ($records) use ($attributor, $method, &$count) {
                foreach ($records as $record) {
                    $attributor->{$method}($record);
                    $count++;
                }
            });
        }

        $this->info(($this->option('dry-run') ? 'Candidats' : 'Enregistrements verifies').': '.$count);
        return self::SUCCESS;
    }
}
