<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tisane;
use League\Csv\Reader;

class TisaneSeeder extends Seeder
{
    public function run()
    {
        $csv = Reader::createFromPath(base_path('database/seeders/tisane_data.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
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
