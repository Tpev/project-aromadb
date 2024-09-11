<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recette;
use League\Csv\Reader;
use Illuminate\Support\Str;

class RecetteSeeder extends Seeder
{
    public function run()
    {
        // Load the CSV file
        $csv = Reader::createFromPath(database_path('seeders/recettes_data.csv'), 'r');
        $csv->setHeaderOffset(0);

        // Iterate through CSV records
        foreach ($csv as $record) {
            Recette::create([
                'REF' => $record['REF'],
                'NomRecette' => $record['NomRecette'],
                'slug' => Str::slug($record['NomRecette']), // Ensure slug is created here
                'TypeApplication' => $record['TypeApplication'],
                'Ingredients' => $record['Ingredients'],
                'Explication' => $record['Explication'],
            ]);
        }
    }
}
