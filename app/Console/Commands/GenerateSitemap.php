<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\User;
use App\Models\HuileHE;
use App\Models\HuileHV;
use App\Models\Tisane;
use App\Models\Recette;
use App\Models\BlogPost;

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
        $sitemap = Sitemap::create();
        $addedPaths = [];

        $addUrl = function (string $path, float $priority = 0.7, $lastModifiedAt = null) use ($sitemap, &$addedPaths): void {
            $normalizedPath = '/' . ltrim($path, '/');
            if (isset($addedPaths[$normalizedPath])) {
                return;
            }

            $url = Url::create(url($normalizedPath))
                ->setPriority($priority);

            if ($lastModifiedAt) {
                $url->setLastModificationDate($lastModifiedAt);
            }

            $sitemap->add($url);
            $addedPaths[$normalizedPath] = true;
        };

        // Core public pages
        $corePages = [
            '/',
            '/pro',
            '/nos-practiciens',
            '/recherche-practicien',
            '/article',
            '/recettes',
            '/huilehes',
            '/huilehvs',
            '/tisanes',
            '/fonctionnalites',
            '/fonctionnalites/agenda',
            '/fonctionnalites/dossiers-clients',
            '/fonctionnalites/facturation',
            '/fonctionnalites/questionnaires',
            '/fonctionnalites/portail-pro',
            '/fonctionnalites/paiements',
            '/metiers/naturopathe',
            '/metiers/sophrologue',
            '/aide/agenda/creer-un-rendez-vous-en-ligne',
            '/aide/agenda/configurer-disponibilites',
            '/aide/agenda/gerer-indisponibilites',
            '/aide/agenda/duree-prestation-temps-de-pause',
            '/aide/agenda/creer-un-atelier-ou-evenement',
            '/aide/agenda/synchroniser-calendrier',
        ];

        foreach ($corePages as $page) {
            $addUrl($page, $page === '/' ? 1.0 : 0.8, now());
        }

        // Public therapist profile pages
        User::query()
            ->where('is_therapist', true)
            ->where('visible_annuarire_admin_set', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(200, function ($therapists) use ($addUrl) {
                foreach ($therapists as $therapist) {
                    $addUrl("/pro/{$therapist->slug}", 0.8, $therapist->updated_at);
                }
            });

        // Resource pages
        HuileHE::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(200, function ($items) use ($addUrl) {
                foreach ($items as $item) {
                    $addUrl("/huilehes/{$item->slug}", 0.9, $item->updated_at);
                }
            });

        HuileHV::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(200, function ($items) use ($addUrl) {
                foreach ($items as $item) {
                    $addUrl("/huilehvs/{$item->slug}", 0.9, $item->updated_at);
                }
            });

        Tisane::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(200, function ($items) use ($addUrl) {
                foreach ($items as $item) {
                    $addUrl("/tisanes/{$item->slug}", 0.9, $item->updated_at);
                }
            });

        Recette::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(200, function ($items) use ($addUrl) {
                foreach ($items as $item) {
                    $addUrl("/recettes/{$item->slug}", 0.9, $item->updated_at);
                }
            });

        BlogPost::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(200, function ($items) use ($addUrl) {
                foreach ($items as $item) {
                    $addUrl("/article/{$item->slug}", 0.8, $item->updated_at);
                }
            });

        // Write final sitemap file
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully with public SEO pages.');
    }
}
