<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chapter;
use League\Csv\Reader;

class ChapterSeeder extends Seeder
{
    public function run()
    {
        $csvPath = base_path('database/seeders/chapter_data.csv');
        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // The first row is the header

        foreach ($csv as $record) {
            Chapter::create([
                'training_id' => $record['training_id'],
                'title'       => $record['title'],
                'position'    => $record['position'],
            ]);
        }
    }
}
