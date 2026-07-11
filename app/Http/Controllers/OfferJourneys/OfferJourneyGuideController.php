<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OfferJourneyGuideController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        $path = base_path('docs/Guide-complet-Parcours-Offre-Olithea.pdf');
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Guide-Parcours-Offre-Olithea.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
