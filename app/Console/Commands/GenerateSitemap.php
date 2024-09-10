<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\HuileHE;
use App\Models\HuileHV;
use App\Models\Tisane;
use App\Models\Recette;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for the website';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0))
            ->add(Url::create('/about')->setPriority(0.8))
            ->add(Url::create('/contact')->setPriority(0.7));

        // Add HuileHE records to sitemap
        $huileHEs = HuileHE::all();
        foreach ($huileHEs as $huileHE) {
            $sitemap->add(Url::create("/huilehes/{$huileHE->id}")
                            ->setLastModificationDate($huileHE->updated_at)
                            ->setPriority(0.9));
        }

        // Add HuileHV records to sitemap
        $huileHVs = HuileHV::all();
        foreach ($huileHVs as $huileHV) {
            $sitemap->add(Url::create("/huilehvs/{$huileHV->id}")
                            ->setLastModificationDate($huileHV->updated_at)
                            ->setPriority(0.9));
        }

        // Add Tisane records to sitemap
        $tisanes = Tisane::all();
        foreach ($tisanes as $tisane) {
            $sitemap->add(Url::create("/tisanes/{$tisane->id}")
                            ->setLastModificationDate($tisane->updated_at)
                            ->setPriority(0.9));
        }

        // Add Recette records to sitemap
        $recettes = Recette::all();
        foreach ($recettes as $recette) {
            $sitemap->add(Url::create("/recettes/{$recette->id}")
                            ->setLastModificationDate($recette->updated_at)
                            ->setPriority(0.9));
        }

        // Write the sitemap to a file
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully.');
    }
}
