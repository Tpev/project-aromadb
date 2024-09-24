<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tisane;
use League\Csv\Reader;

class TisaneSeeder extends Seeder
{
    public function run()
    {
        try {
            // Load the CSV file
            $csv = Reader::createFromPath(base_path('database/seeders/tisane_data.csv'), 'r');
            $csv->setHeaderOffset(0);
        } catch (Exception $e) {
            echo "Error loading CSV file: " . $e->getMessage() . PHP_EOL;
            return;
        }

        // Get all records from the CSV file and convert them to an array
        $records = iterator_to_array($csv);

        // Sort the records by 'NomTisane'
        usort($records, function ($a, $b) {
            return strcmp($a['NomTisane'], $b['NomTisane']);
        });

        // Iterate through sorted records and insert them into the database
        foreach ($records as $record) {
            Tisane::create([
                'REF' => $record['REF'],
                'NomTisane' => $record['NomTisane'],
                'NomLatin' => $record['NomLatin'],
                'Provenance' => $record['Provenance'],
                'OrganeProducteur' => $record['OrganeProducteur'],
                'Sb' => $record['Sb'],
                'Properties' => $record['Properties'],
                'Indications' => $record['Indications'],
                'ContreIndications' => $record['ContreIndications'],
                'Note' => $record['Note'],
                'Description' => $record['Description'],
            ]);
        }
    }
}
