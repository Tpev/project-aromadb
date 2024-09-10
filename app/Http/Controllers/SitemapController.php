<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $file = public_path('sitemap.xml');

        if (!file_exists($file)) {
            return response()->json(['error' => 'Sitemap not found.'], 404);
        }

        return Response::make(file_get_contents($file), 200)
            ->header('Content-Type', 'application/xml');
    }
}
