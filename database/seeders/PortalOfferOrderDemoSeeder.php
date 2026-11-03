<?php

namespace Database\Seeders;

use App\Models\PackProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortalOfferOrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Includes the dedicated local database and log-only mail guard.
        $this->call(PackPhotoDemoSeeder::class);
        $therapist = User::where('email', 'therapeute.avis@olithea.test')->sole();
        $therapist->update(['portal_offer_order' => 'prestations_first']);

        foreach ([
            ['Bien-être & relaxation', 10, 'Séance détente à l’unité (démo)', 60, 60, 'Pack Sérénité · 3 séances'],
            ['Découverte', 20, 'Bilan découverte à l’unité (démo)', 45, 55, 'Pack Découverte · sans photo'],
        ] as [$categoryName, $order, $productName, $duration, $price, $packName]) {
            $category = $therapist->productCategories()->firstOrCreate(['name' => $categoryName], [
                'display_order' => $order,
            ]);
            Product::firstOrCreate(['user_id' => $therapist->id, 'name' => $productName], [
                'product_category_id' => $category->id,
                'description' => 'Une séance individuelle pour découvrir l’accompagnement. Exemple fictif pour la revue du portail.',
                'price' => $price, 'tax_rate' => 0, 'duration' => $duration,
                'dans_le_cabinet' => true, 'can_be_booked_online' => false, 'collect_payment' => false,
                'visible_in_portal' => true, 'price_visible_in_portal' => true,
            ]);
            PackProduct::where('user_id', $therapist->id)->where('name', $packName)
                ->update(['product_category_id' => $category->id]);
        }

        $this->command?->line('Prestations individuelles d’abord : '.route('therapist.show', $therapist->slug));
        $this->command?->line('Réglage : '.route('profile.editCompanyInfo').' (onglet Profil public)');
    }
}
