<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sharifuddin\ImageCropper\Facades\ImageCropper;

class ImageUploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $base64Image = $request->input('image');
        
        // Save image using the package
        $path = ImageCropper::saveImage($base64Image);
        
        // Get public URL
        $url = ImageCropper::getPublicUrl($path);
        
        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => $url
        ]);
    }
}