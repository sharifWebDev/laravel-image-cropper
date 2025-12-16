<?php

namespace LaravelImageCropper\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }
}
