<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class TestCertificateController extends Controller
{
    public function generateTestCertificate()
    {
        // Instantiate ImageManager with the 'gd' driver explicitly
        $manager = new ImageManager('gd');

        // Path to the template certificate image
        $certificatePath = public_path('images/certificat_template.png');

        // Read image from the filesystem
        $image = $manager->make($certificatePath);

        // Static fullff name for testing
        $fullName = 'Jean Dupont';

        // Path to font file (ensure it exists in the specified location)
        $fontPath = public_path('fonts/Roboto-Regular.ttf');

        // Add static text to the certificate
        $image->text($fullName, 335, 270, function ($font) use ($fontPath) {
            $font->file($fontPath);
            $font->size(40); // Font size
            $font->color('#5E6A0D'); // Text color to match the design
            $font->align('center');
            $font->valign('middle');
        });

        // Save the modified certificate image
        $fileName = 'test_certificate.png';
        $filePath = 'public/certificates/' . $fileName;
        Storage::put($filePath, (string) $image->encode('png'));

        // Get the URL for the saved certificate
        $certificateUrl = Storage::url($filePath);
        
        return view('test.certificate', compact('certificateUrl'));
    }
}
