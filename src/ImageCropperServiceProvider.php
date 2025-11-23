<?php

namespace Sharifuddin\ImageCropper;

use Illuminate\Support\ServiceProvider;

class ImageCropperServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'image-cropper');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/image-cropper'),
        ], 'image-cropper-views');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/image-cropper'),
        ], 'image-cropper-assets');

        $this->publishes([
            __DIR__.'/../config/image-cropper.php' => config_path('image-cropper.php'),
        ], 'image-cropper-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/image-cropper.php', 'image-cropper');
    }
}
