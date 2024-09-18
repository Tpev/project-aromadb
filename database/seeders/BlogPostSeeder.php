<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use League\Csv\Reader;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run()
    {
        // Path to the CSV file containing the blog post data
        $csv = Reader::createFromPath(base_path('database/seeders/blog_posts_data.csv'), 'r');
        $csv->setHeaderOffset(0); // Set the CSV headers

        // Loop through each record in the CSV and create a new BlogPost
        foreach ($csv as $record) {
            BlogPost::create([
                'REF' => $record['REF'],
                'Title' => $record['Title'],
                'Tags' => $record['Tags'],
                'Contents' => $record['Contents'],
                'RelatedPostsREF' => $record['RelatedPostsREF'],
                'MetaDescription' => $record['MetaDescription'],
            ]);
        }
    }
}
