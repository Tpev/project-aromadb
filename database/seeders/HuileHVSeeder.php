<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HuileHV;
use League\Csv\Reader;

class HuileHVSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load the CSV file
        $csv = Reader::createFromPath(base_path('database/seeders/huilehv.csv'), 'r');
        $csv->setHeaderOffset(0);

        // Iterate through CSV records
        foreach ($csv as $record) {
            HuileHV::create([
                'REF' => $record['REF'],
                'NomHV' => $record['NomHV'],
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
