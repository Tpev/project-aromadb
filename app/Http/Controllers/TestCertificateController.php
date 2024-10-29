<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class TestCertificateController extends Controller
{
    public function generateTestCertificate()
    {
        // Load the base certificate image
        $image = Image::make(public_path('images/certificat.png'));

        // Write text on the image
        $image->text('The quick brown fox', 120, 100, function ($font) {
            $font->file(public_path('images/Roboto-Regular.ttf'));
            $font->size(70);
            $font->color('#FFFFFF');
            $font->align('center');
            $font->valign('middle');
            $font->angle(10);
        });

        // Define the path to save the generated image
        $filename = 'certificates/generated_certificate.png';
        $path = public_path($filename);

        // Save the image to the public directory
        $image->save($path);

        // Pass the relative path to the view
        return view('test.certificate', ['imagePath' => $filename]);
    }
}
