<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tisane;
use League\Csv\Reader;

class TisaneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load the CSV file
        $csv = Reader::createFromPath(base_path('database/seeders/tisane_data.csv'), 'r');
        $csv->setHeaderOffset(0);

        // Iterate through CSV records
        foreach ($csv as $record) {
            Tisane::create([
                'REF' => $record['REF'],
                'NomTisane' => $record['NomTisane'],
                'NomLatin' => $record['NomLatin'],
                'Provenance' => $record['Provenance'],
                'Properties' => $record['Properties'],
                'Indications' => $record['Indications'],
                'ContreIndications' => $record['ContreIndications'],
                'Description' => $record['Description'],
            ]);
        }
    }
}
