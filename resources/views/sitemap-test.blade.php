<?php
use Illuminate\Support\Str;

$specialties = [
    "Hypnothérapeute",
    "Sophrologue",
    "Massage bien-être",
    "Réflexologue",
    "Naturopathe",
    "Psychopraticien",
    "Coach de vie",
    "Ostéopathe",
    "Diététicien Nutritionniste",
    "Chiropracteur",
    "Médecin acupuncteur",
    "Psychologue",
    "Coach PNL",
    "Coach professionnel",
    "Enseignant en méditation",
    "Professeur de Yoga",
    "Praticien EFT",
    "Kinésiologue",
    "Relaxologue",
    "Aromathérapeute",
    "Énergétique Traditionnelle Chinoise",
    "Sexologue",
    "Sonothérapeute",
    "Fasciathérapeute",
    "Neurothérapeute",
    "Herboriste",
    "Psychanalyste",
    "Art-thérapeute",
    "Psychomotricien",
    "Phytothérapeute",
    "Etiopathe",
    "Posturologue",
    "Professeur de Pilates",
    "Coach parental et familial",
    "Danse-thérapeute",
    "Musicothérapeute",
    "Praticien en Ayurvéda",
    "Praticien en Gestalt",
    "Praticien en thérapies brèves",
    "Yoga thérapie",
    "Somatopathe",
    "Praticien massage Shiatsu"
];

$regions = [
    "Île-de-France",
    "Provence-Alpes-Côte d'Azur",
    "Nouvelle-Aquitaine",
    "Occitanie",
    "Hauts-de-France",
    "Grand Est",
    "Bretagne",
    "Normandie",
    "Pays de la Loire",
    "Centre-Val de Loire",
    "Corse"
];

$baseUrl = url('/');
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Page d'accueil / liste de tous les thérapeutes -->
    <url>
        <loc>{{ $baseUrl }}/therapeutes</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Routes pour chaque spécialité seule -->
    @foreach($specialties as $specialty)
        <url>
            <loc>{{ $baseUrl }}/practicien-{{ Str::slug($specialty) }}</loc>
            <lastmod>{{ now()->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    <!-- Routes pour chaque région seule -->
    @foreach($regions as $region)
        <url>
            <loc>{{ $baseUrl }}/region-{{ Str::slug($region) }}</loc>
            <lastmod>{{ now()->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    <!-- Routes pour les combinaisons spécialité et région -->
    @foreach($specialties as $specialty)
        @foreach($regions as $region)
            <url>
                <loc>{{ $baseUrl }}/practicien-{{ Str::slug($specialty) }}-region-{{ Str::slug($region) }}</loc>
                <lastmod>{{ now()->toAtomString() }}</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.6</priority>
            </url>
        @endforeach
    @endforeach
</urlset>
