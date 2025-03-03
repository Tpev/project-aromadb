<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;
use League\Csv\Reader;

class LessonSeeder extends Seeder
{
    public function run()
    {
        $csvPath = base_path('database/seeders/lesson_data.csv');
        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // The first row is the header

        foreach ($csv as $record) {
            Lesson::create([
                'chapter_id' => $record['chapter_id'],
                'title'      => $record['title'],
                'content'    => $record['content'],
                'position'   => $record['position'],
            ]);
        }
    }
}
