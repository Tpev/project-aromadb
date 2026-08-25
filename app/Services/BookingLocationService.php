<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Product;
use App\Models\SpecialAvailability;
use App\Models\User;
use Illuminate\Support\Collection;

class BookingLocationService
{
    public function __construct(
        private readonly CabinetAccessService $cabinetAccess,
        private readonly BookingSchedulingPolicy $schedulingPolicy,
    ) {
    }

    /**
     * @param Collection<int, Product> $products
     * @return array<int, array<int, array{id:int,label:string,address:string}>>
     */
    public function compatibleLocationsByProduct(User $practitioner, Collection $products): array
    {
        $accessible = $this->cabinetAccess->accessibleLocations($practitioner)->keyBy('id');
        $accessibleIds = $accessible->keys()->map(fn ($id): int => (int) $id)->all();

        $weekly = $accessibleIds === []
            ? collect()
            : Availability::query()
                ->with('products:id')
                ->where('user_id', $practitioner->id)
                ->whereIn('practice_location_id', $accessibleIds)
                ->get();
        $special = $accessibleIds === []
            ? collect()
            : SpecialAvailability::query()
                ->with('products:id')
                ->where('user_id', $practitioner->id)
                ->whereDate('date', '>=', today()->toDateString())
                ->whereIn('practice_location_id', $accessibleIds)
                ->get();
        $availabilityPeriods = $weekly->concat($special);

        return $products->mapWithKeys(function (Product $product) use ($accessible, $accessibleIds, $availabilityPeriods): array {
            if ($accessibleIds === [] || ! $this->schedulingPolicy->productSupportsMode($product, 'cabinet')) {
                return [$product->id => []];
            }

            $compatibleIds = $availabilityPeriods
                ->filter(fn ($period): bool => $period->applies_to_all
                    || $period->products->contains('id', $product->id))
                ->pluck('practice_location_id')
                ->filter()
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->all();

            $locations = $accessible
                ->only($compatibleIds)
                ->values()
                ->map(fn ($location): array => [
                    'id' => (int) $location->id,
                    'label' => (string) $location->label,
                    'address' => (string) $location->full_address,
                ])
                ->all();

            return [$product->id => $locations];
        })->all();
    }

    /**
     * @param Collection<int, Product> $products
     * @return array<int, int>
     */
    public function publiclyBookableProductIds(User $practitioner, Collection $products): array
    {
        $locationsByProduct = $this->compatibleLocationsByProduct($practitioner, $products);

        return $products
            ->filter(function (Product $product) use ($locationsByProduct): bool {
                if (! $product->can_be_booked_online) {
                    return false;
                }

                if ($product->visio || $product->adomicile || $product->en_entreprise) {
                    return true;
                }

                return $product->dans_le_cabinet
                    && ($locationsByProduct[$product->id] ?? []) !== [];
            })
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }
}
