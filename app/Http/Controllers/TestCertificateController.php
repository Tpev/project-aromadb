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
$image->text('The quick brown fox', 120, 100, function (FontFactory $font) {
    $font->filename('images/Roboto-Regular.ttf');
    $font->size(70);
    $font->color('fff');
    $font->stroke('ff5500', 2);
    $font->align('center');
    $font->valign('middle');
    $font->lineHeight(1.6);
    $font->angle(10);
    $font->wrap(250);
});
        
        return view('test.certificate', compact('certificateUrl'));
    }
}
