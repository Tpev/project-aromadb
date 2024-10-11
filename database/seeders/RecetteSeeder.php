<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recette;
use League\Csv\Reader;
use Illuminate\Support\Str;
use Exception;

class RecetteSeeder extends Seeder
{
    public function run()
    {
        try {
            // Load the CSV file
            $csv = Reader::createFromPath(database_path('seeders/recettes_data.csv'), 'r');
            $csv->setHeaderOffset(0);
        } catch (Exception $e) {
            echo "Error loading CSV file: " . $e->getMessage() . PHP_EOL;
            return;
        }

        // Get all records from the CSV file and convert them to an array
        $records = iterator_to_array($csv);

        // Sort the records by 'NomRecette'
        usort($records, function ($a, $b) {
            return strcmp($a['NomRecette'], $b['NomRecette']);
        });

        // Iterate through sorted records
        foreach ($records as $record) {
            // Validate required fields
            if (!isset($record['NomRecette'], $record['TypeApplication'], $record['REF'])) {
                echo "Missing required fields for record: " . json_encode($record) . PHP_EOL;
                continue;
            }

            // Construct the initial slug
            $baseSlug = Str::slug($record['NomRecette'] . '-' . $record['TypeApplication'] . '-' . $record['REF']);
            $slug = $baseSlug;

            // Output the generated base slug to the terminal
            echo "Generated baseSlug: " . $baseSlug . PHP_EOL;

            // Ensure slug is unique by checking existing records and appending a number if necessary
            $counter = 1;
            while (Recette::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Output the final slug after ensuring uniqueness
            echo "Final slug: " . $slug . PHP_EOL;

            // Log the actual values being inserted into the database
            echo "Inserting Recette: REF: {$record['REF']}, Slug: {$slug}" . PHP_EOL;

            // Create the recette record
            try {
                Recette::create([
                    'REF' => $record['REF'],
                    'NomRecette' => $record['NomRecette'],
                    'slug' => $slug, // Ensure the slug is unique
                    'TypeApplication' => $record['TypeApplication'],
                    'IngredientsHE' => $record['IngredientsHE'] ?? null, // Handle possible missing fields
                    'IngredientsHV' => $record['IngredientsHV'] ?? null,
                    'IngredientsTisane' => $record['IngredientsTisane'] ?? null,
                    'Explication' => $record['Explication'] ?? null,
                    'note' => $record['Note'] ?? null,
                ]);
            } catch (Exception $e) {
                echo "Error inserting record: " . $e->getMessage() . PHP_EOL;
            }
        }
    }
}
