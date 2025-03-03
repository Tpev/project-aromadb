<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training;
use League\Csv\Reader;

class TrainingSeeder extends Seeder
{
    public function run()
    {
        // Path to CSV file
        $csvPath = base_path('database/seeders/training_data.csv');
        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // The first row is the header

        foreach ($csv as $record) {
            // Adjust the array keys to match the column headers in your CSV
            Training::create([
                'title'       => $record['title'],
                'description' => $record['description'],
            ]);
        }
    }
}
