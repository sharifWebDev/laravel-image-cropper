<?php

use Intervention\Image\ImageManagerStatic as Image;

if (!function_exists('saveImage')) {
    /**
     * Save base64 image to storage with optional format, quality, and filename
     *
     * @param string $base64
     * @param string|null $folder
     * @param int|null $quality
     * @param string|null $format 'webp', 'jpg', 'png', or null to keep original
     * @param string|null $filename Optional filename without extension
     * @return string Public path
     */
    function saveImage(
        string $base64,
        ?string $folder = null,
        ?int $quality = null,
        ?string $format = null,
        ?string $filename = null
    ): string {
        $folder = $folder ?? config('image-cropper.default_folder', 'uploads');
        $quality = $quality ?? config('image-cropper.default_quality', 90);
        $format = $format ?? config('image-cropper.default_format', 'webp');

        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $originalExtension = strtolower($type[1]);
        } else {
            $originalExtension = 'png';
        }

        $binary = base64_decode($base64);
        $finalFormat = $format ?? $originalExtension;
        $extension = $finalFormat === 'jpg' ? 'jpg' : $finalFormat;

        $image = Image::make($binary)->orientate();

        $dir = storage_path('app/public/' . $folder);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = $filename ? $filename . '.' . $extension : uniqid() . '.' . $extension;

        if (in_array($finalFormat, ['jpg', 'jpeg', 'webp'])) {
            $image->save($dir . '/' . $filename, $quality, $finalFormat);
        } else {
            $image->save($dir . '/' . $filename);
        }

        return 'storage/' . $folder . '/' . $filename;
    }
}
