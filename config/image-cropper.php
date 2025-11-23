<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Storage Folder
    |--------------------------------------------------------------------------
    |
    | The default folder under `storage/app/public` where images will be saved.
    |
    */

    'default_folder' => env('IMAGE_CROPPER_FOLDER', 'uploads'),

    /*
    |--------------------------------------------------------------------------
    | Default Image Quality
    |--------------------------------------------------------------------------
    |
    | Default quality for saving JPG/WebP images. Value should be between 0-100.
    |
    */

    'default_quality' => env('IMAGE_CROPPER_QUALITY', 90),

    /*
    |--------------------------------------------------------------------------
    | Default Image Format
    |--------------------------------------------------------------------------
    |
    | Default format to save images. Supported: 'webp', 'jpg', 'png', null (keep original).
    |
    */

    'default_format' => env('IMAGE_CROPPER_FORMAT', 'webp'),

    /*
    |--------------------------------------------------------------------------
    | Default Ratios
    |--------------------------------------------------------------------------
    |
    | Ratios available for the cropper. Keys are labels, values are ratio numbers.
    |
    */

    'default_ratios' => [
        '1:1' => 1,
        '16:9' => 16 / 9,
        '4:5' => 4 / 5,
        '4:3' => 4 / 3,
        '2:3' => 2 / 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Show Ratio Selector
    |--------------------------------------------------------------------------
    |
    | If true, the cropper will show ratio options in the UI. If false, ratio selection is hidden.
    |
    */

    'enable_ratio' => env('IMAGE_CROPPER_ENABLE_RATIO', true),

    /*
    |--------------------------------------------------------------------------
    | Enable Cropping
    |--------------------------------------------------------------------------
    |
    | If true, users can crop image before upload. If false, direct upload.
    |
    */

    'enable_crop' => env('IMAGE_CROPPER_ENABLE_CROP', true),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The disk to use for saving images. Default is 'public'.
    |
    */

    'disk' => env('IMAGE_CROPPER_DISK', 'public'),
];
