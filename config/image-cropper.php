<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    |
    | This option controls the default storage disk that will be used to store
    | uploaded images. You may set this to any of the disks defined in your
    | config/filesystems.php configuration file.
    |
    */

    'disk' => env('IMAGE_CROPPER_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Default Folder
    |--------------------------------------------------------------------------
    |
    | This is the default folder where uploaded images will be stored.
    |
    */

    'default_folder' => env('IMAGE_CROPPER_FOLDER', 'uploads/images'),

    /*
    |--------------------------------------------------------------------------
    | Default Image Format
    |--------------------------------------------------------------------------
    |
    | This option controls the default format for saved images.
    | Supported: 'webp', 'jpg', 'png', 'original'
    |
    */

    'default_format' => env('IMAGE_CROPPER_FORMAT', 'webp'),

    /*
    |--------------------------------------------------------------------------
    | Default Image Quality
    |--------------------------------------------------------------------------
    |
    | This option controls the default quality for saved images (0-100).
    |
    */

    'default_quality' => env('IMAGE_CROPPER_QUALITY', 90),

    /*
    |--------------------------------------------------------------------------
    | Enable Cropping
    |--------------------------------------------------------------------------
    |
    | This option controls whether cropping is enabled by default.
    |
    */

    'enable_crop' => env('IMAGE_CROPPER_ENABLE_CROP', true),

    /*
    |--------------------------------------------------------------------------
    | Enable Ratio Selection
    |--------------------------------------------------------------------------
    |
    | This option controls whether ratio selection is enabled by default.
    |
    */

    'enable_ratio' => env('IMAGE_CROPPER_ENABLE_RATIO', true),

    /*
    |--------------------------------------------------------------------------
    | Default Aspect Ratios
    |--------------------------------------------------------------------------
    |
    | This option defines the default aspect ratios available for selection.
    | Use 'Free' for free cropping (no fixed ratio).
    |
    */

    'default_ratios' => [
        'Free' => null,
        'Square 1:1' => 1,
        'Landscape 16:9' => 16/9,
        'Portrait 9:16' => 9/16,
        '4:3' => 4/3,
        '3:2' => 3/2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Route
    |--------------------------------------------------------------------------
    |
    | This option defines the route for image uploads.
    |
    */

    'upload_route' => env('IMAGE_CROPPER_UPLOAD_ROUTE', '/image-upload'),

    /*
    |--------------------------------------------------------------------------
    | Maximum File Size (in MB)
    |--------------------------------------------------------------------------
    |
    | This option controls the maximum file size for uploads.
    |
    */

    'max_file_size' => env('IMAGE_CROPPER_MAX_SIZE', 10),

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    |
    | This option controls the allowed file types for uploads.
    |
    */

    'allowed_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ],
];