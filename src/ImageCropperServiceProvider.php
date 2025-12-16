<?php

namespace Sharifuddin\ImageCropper;

use Illuminate\Support\ServiceProvider;

class ImageCropperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/image-cropper.php', 'image-cropper'
        );

        $this->app->singleton('image-cropper', function ($app) {
            return new ImageCropper($app['config']->get('image-cropper'));
        });
    }

    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/image-cropper.php' => config_path('image-cropper.php'),
        ], 'image-cropper-config');

        // Publish views
        $this->publishes([
            __DIR__.'/Views' => resource_path('views/vendor/image-cropper'),
        ], 'image-cropper-views');

        // Publish assets
        $this->publishes([
            __DIR__.'/Resources/js' => public_path('vendor/image-cropper/js'),
            __DIR__.'/Resources/css' => public_path('vendor/image-cropper/css'),
        ], 'image-cropper-assets');

        // Load views
        $this->loadViewsFrom(__DIR__.'/Views', 'image-cropper');

        // Register blade component
        $this->loadViewComponentsAs('image-cropper', [
            Components\ImageCropper::class,
        ]);

        // Register routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}