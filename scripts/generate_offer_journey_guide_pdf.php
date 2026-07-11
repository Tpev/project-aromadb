<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$source = $root.'/docs/guide-parcours-offre-pdf.html';
$destination = $root.'/docs/Guide-complet-Parcours-Offre-Olithea.pdf';

$html = file_get_contents($source);
if ($html === false) {
    throw new RuntimeException('Impossible de lire le guide HTML.');
}

$html = preg_replace_callback('/data-local-src="([^"]+)"/', function (array $match) use ($root): string {
    $path = realpath($root.'/'.str_replace('/', DIRECTORY_SEPARATOR, $match[1]));
    if ($path === false || ! str_starts_with($path, $root)) {
        throw new RuntimeException('Image locale introuvable: '.$match[1]);
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException('Impossible de lire image: '.$match[1]);
    }

    $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        default => throw new RuntimeException('Format image non pris en charge: '.$path),
    };

    return 'src="data:'.$mime.';base64,'.base64_encode($contents).'"';
}, $html);

if (! is_string($html)) {
    throw new RuntimeException('Impossible de préparer le guide.');
}

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot($root);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

if (file_put_contents($destination, $dompdf->output()) === false) {
    throw new RuntimeException('Impossible de créer le PDF.');
}

fwrite(STDOUT, $destination.PHP_EOL);
