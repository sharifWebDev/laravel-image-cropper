<?php

namespace Sharifuddin\ImageCropper\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sharifuddin\ImageCropper\Facades\ImageCropper;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {
            $path = ImageCropper::saveImage($request->input('image'));
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => ImageCropper::getPublicUrl($path),
                'message' => 'Image uploaded successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}