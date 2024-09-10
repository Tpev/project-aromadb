<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HuileHE;
use League\Csv\Reader;
use Illuminate\Support\Str;

class HuileHESeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load the CSV file
        $csv = Reader::createFromPath(base_path('database/seeders/huile_he_data.csv'), 'r');
        $csv->setHeaderOffset(0);

        // Iterate through CSV records
        foreach ($csv as $record) {
            $originalSlug = Str::slug($record['NomHE']);  // Generate a slug from the NomHE
            $slug = $originalSlug;
            $count = 1;

            // Ensure the slug is unique by appending a counter if needed
            while (HuileHE::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }

            HuileHE::create([
                'REF' => $record['REF'],
                'NomHE' => $record['NomHE'],
                'NomLatin' => $record['NomLatin'],
                'Provenance' => $record['Provenance'],
                'OrganeProducteur' => $record['OrganeProducteur'],
                'Sb' => $record['Sb'],
                'Properties' => $record['Properties'],
                'Indications' => $record['Indications'],
                'ContreIndications' => $record['ContreIndications'],
                'Note' => $record['Note'],
                'Description' => $record['Description'],
                'slug' => $slug,  // Add the generated slug here
            ]);
        }
    }
}
