<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class TestCertificateController extends Controller
{
    public function generateTestCertificate()
    {



// create test image
$image = ImageManager::imagick()->read('images/certificat.png');

// write text to image
$image->text('Thibaud Peverelli', 950, 600, function (FontFactory $font) {
    $font->filename('images/Roboto-Regular.ttf');
    $font->size(70);
    $font->color('854f38');
    $font->stroke('854f38', 2);
    $font->align('center');
    $font->valign('middle');
    $font->lineHeight(1.6);


});
// Define the path to save the generated image
        $filename = 'certificates/generated_certificate.png';
        $path = public_path($filename);

        // Save the image to the public directory
        $image->save($path); 
         return view('test.certificate', ['imagePath' => $filename]);
    }
}
