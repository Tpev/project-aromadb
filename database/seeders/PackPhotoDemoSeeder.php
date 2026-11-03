<?php

namespace Database\Seeders;

use App\Models\PackProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\PackPhotoService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class PackPhotoDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Enforces local environment, dedicated demo SQLite database and log-only mail.
        $this->call(ReviewRequestDemoSeeder::class);
        $therapist = User::where('email', 'therapeute.avis@olithea.test')->sole();
        $product = Product::firstOrCreate([
            'user_id' => $therapist->id, 'name' => 'Séance de relaxation (démo pack)',
        ], [
            'price' => 60, 'tax_rate' => 0, 'duration' => 60, 'dans_le_cabinet' => true,
            'can_be_booked_online' => false, 'collect_payment' => false, 'visible_in_portal' => false,
        ]);

        foreach ([['Pack Sérénité · 3 séances', 3, 165, true], ['Pack Découverte · sans photo', 2, 110, false]] as [$name, $quantity, $price, $withPhoto]) {
            $pack = PackProduct::firstOrCreate(['user_id' => $therapist->id, 'name' => $name], [
                'description' => 'Un temps pour vous : '.$quantity.' séances de relaxation et un accompagnement personnalisé. Pack fictif pour la démonstration locale.',
                'price' => $price, 'tax_rate' => 0, 'is_active' => true, 'visible_in_portal' => true,
                'price_visible_in_portal' => true, 'installments_enabled' => false,
            ]);
            $pack->items()->firstOrCreate(['product_id' => $product->id], ['quantity' => $quantity, 'sort_order' => 0]);
            if ($withPhoto && ! $pack->photo_path) {
                app(PackPhotoService::class)->save((int) $therapist->id, $pack, [
                    'photo' => new UploadedFile(database_path('seeders/fixtures/pack-serenite.webp'), 'pack-serenite.webp', 'image/webp', null, true),
                ], function (array $attributes) use ($pack): PackProduct {
                    $pack->update($attributes);

                    return $pack;
                });
            }
            $this->command?->line($name.' : '.route('pack-products.edit', $pack));
        }
        $this->command?->line('Portail : '.route('therapist.show', $therapist->slug));
    }
}
