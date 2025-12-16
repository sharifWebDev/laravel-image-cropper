<?php

namespace LaravelImageCropper\Facades;

use Illuminate\Support\Facades\Facade;

class ImageCropper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'image-cropper';
    }
}
