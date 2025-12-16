<?php

namespace Sharifuddin\ImageCropper\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sharifuddin\ImageCropper\ImageCropperServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ImageCropperServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'ImageCropper' => \Sharifuddin\ImageCropper\Facades\ImageCropper::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('image-cropper', [
            'disk' => 'public',
            'default_folder' => 'uploads/images',
            'default_format' => 'webp',
            'default_quality' => 90,
            'enable_crop' => true,
            'enable_ratio' => true,
            'default_ratios' => [
                'Free' => null,
                'Square 1:1' => 1,
                'Landscape 16:9' => 16/9,
                'Portrait 9:16' => 9/16,
                '4:3' => 4/3,
                '3:2' => 3/2,
            ],
            'upload_route' => '/image-upload',
        ]);
    }
}