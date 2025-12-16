<?php

namespace Sharifuddin\ImageCropper;

use InvalidArgumentException;
use RuntimeException;

class ImageCropper
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function config()
    {
        return $this->config;
    }

    public function getDefaultRatios()
    {
        return $this->config['default_ratios'];
    }

    public function getUploadRoute()
    {
        return $this->config['upload_route'] ?? '/image-upload';
    }

    public function getDefaultFormat()
    {
        return $this->config['default_format'];
    }

    public function getDefaultQuality()
    {
        return $this->config['default_quality'];
    }

    public function isClientSideOnly()
    {
        return $this->config['client_side_only'] ?? true;
    }

    public function shouldAutoProcessForms()
    {
        return $this->config['auto_process_forms'] ?? true;
    }

    /**
     * Save base64 image to storage
     */
    public function saveImage(
        string $base64,
        ?string $folder = null,
        ?int $quality = null,
        ?string $format = null,
        ?string $filename = null
    ): string {
        $folder = $folder ?? $this->config['default_folder'];
        $quality = $quality ?? $this->config['default_quality'];
        $format = $format ?? $this->config['default_format'];
        $disk = $this->config['disk'];

        // Extract base64 data and original format
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $originalExtension = strtolower($type[1]);
        } else {
            throw new InvalidArgumentException('Invalid base64 image format');
        }

        $binary = base64_decode($base64);
        if ($binary === false) {
            throw new InvalidArgumentException('Invalid base64 data');
        }

        // Determine final format and extension
        if ($format === 'original') {
            $finalFormat = $originalExtension;
        } else {
            $finalFormat = $format;
        }

        $extension = $finalFormat === 'jpg' ? 'jpg' : $finalFormat;

        // Generate filename
        $filename = $filename ? $filename . '.' . $extension : uniqid() . '.' . $extension;

        // Ensure directory exists
        $storagePath = 'app/' . $disk . '/' . $folder;
        $fullPath = storage_path($storagePath);
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Save image directly without processing
        $filePath = $fullPath . '/' . $filename;
        
        // Simply write the binary data to file
        if (file_put_contents($filePath, $binary) === false) {
            throw new RuntimeException('Failed to save image to storage');
        }

        return $folder . '/' . $filename;
    }

    /**
     * Save base64 video to storage
     */
    public function saveVideo(
        string $base64,
        ?string $folder = null,
        ?string $filename = null,
        int $maxSizeGB = 10
    ): string {
        $folder = $folder ?? $this->config['default_folder'] . '/videos';
        $disk = $this->config['disk'];

        // Extract base64 data and original format
        if (preg_match('/^data:video\/(\w+);base64,/', $base64, $type)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $extension = strtolower($type[1]);
        } else {
            throw new InvalidArgumentException('Invalid base64 video format');
        }

        // Calculate file size before decoding
        $fileSize = (int) (strlen($base64) * 3 / 4);
        $maxSizeBytes = $maxSizeGB * 1024 * 1024 * 1024;

        if ($fileSize > $maxSizeBytes) {
            throw new InvalidArgumentException(
                "Video file too large. Maximum allowed: {$maxSizeGB}GB"
            );
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            throw new InvalidArgumentException('Invalid base64 data');
        }

        // Generate filename
        $filename = $filename ? $filename . '.' . $extension : uniqid() . '.' . $extension;

        // Ensure directory exists
        $storagePath = 'app/' . $disk . '/' . $folder;
        $fullPath = storage_path($storagePath);
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Save video with stream handling for large files
        $filePath = $fullPath . '/' . $filename;
        
        if ($fileSize > 100 * 1024 * 1024) { // For files > 100MB, use streaming
            if (!$this->saveLargeBase64File($base64, $filePath)) {
                throw new RuntimeException('Failed to save large video file');
            }
        } else {
            if (file_put_contents($filePath, $binary) === false) {
                throw new RuntimeException('Failed to save video to storage');
            }
        }

        return $folder . '/' . $filename;
    }

    /**
     * Save base64 audio to storage
     */
    public function saveAudio(
        string $base64,
        ?string $folder = null,
        ?string $filename = null,
        int $maxSizeMB = 500
    ): string {
        $folder = $folder ?? $this->config['default_folder'] . '/audio';
        $disk = $this->config['disk'];

        // Extract base64 data and original format
        if (preg_match('/^data:audio\/(\w+);base64,/', $base64, $type)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $extension = strtolower($type[1]);
        } else {
            throw new InvalidArgumentException('Invalid base64 audio format');
        }

        // Calculate file size before decoding
        $fileSize = (int) (strlen($base64) * 3 / 4);
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;

        if ($fileSize > $maxSizeBytes) {
            throw new InvalidArgumentException(
                "Audio file too large. Maximum allowed: {$maxSizeMB}MB"
            );
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            throw new InvalidArgumentException('Invalid base64 data');
        }

        // Generate filename
        $filename = $filename ? $filename . '.' . $extension : uniqid() . '.' . $extension;

        // Ensure directory exists
        $storagePath = 'app/' . $disk . '/' . $folder;
        $fullPath = storage_path($storagePath);
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Save audio
        $filePath = $fullPath . '/' . $filename;
        
        if ($fileSize > 50 * 1024 * 1024) { // For files > 50MB
            if (!$this->saveLargeBase64File($base64, $filePath)) {
                throw new RuntimeException('Failed to save large audio file');
            }
        } else {
            if (file_put_contents($filePath, $binary) === false) {
                throw new RuntimeException('Failed to save audio to storage');
            }
        }

        return $folder . '/' . $filename;
    }

    /**
     * Save base64 file to storage (for any file type)
     */
    public function saveFile(
        string $base64,
        ?string $folder = null,
        ?string $mimeType = null,
        ?string $filename = null,
        int $maxSizeMB = 2048
    ): string {
        $folder = $folder ?? $this->config['default_folder'] . '/files';
        $disk = $this->config['disk'];

        $extension = 'bin'; // Default extension

        // Extract base64 data and detect MIME type
        if (preg_match('/^data:([\w\/]+);base64,/', $base64, $type)) {
            $mimeType = $type[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        }

        // Calculate file size before decoding
        $fileSize = (int) (strlen($base64) * 3 / 4);
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;

        if ($fileSize > $maxSizeBytes) {
            throw new InvalidArgumentException(
                "File too large. Maximum allowed: {$maxSizeMB}MB"
            );
        }

        // Determine extension from MIME type
        if ($mimeType) {
            $extension = $this->mimeToExtension($mimeType);
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            throw new InvalidArgumentException('Invalid base64 data');
        }

        // Generate filename
        $filename = $filename ? $filename . '.' . $extension : uniqid() . '.' . $extension;

        // Ensure directory exists
        $storagePath = 'app/' . $disk . '/' . $folder;
        $fullPath = storage_path($storagePath);
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Save file
        $filePath = $fullPath . '/' . $filename;
        
        if ($fileSize > 100 * 1024 * 1024) { // For files > 100MB
            if (!$this->saveLargeBase64File($base64, $filePath)) {
                throw new RuntimeException('Failed to save large file');
            }
        } else {
            if (file_put_contents($filePath, $binary) === false) {
                throw new RuntimeException('Failed to save file to storage');
            }
        }

        return $folder . '/' . $filename;
    }

    /**
     * Save large base64 files using streaming to avoid memory issues
     */
    protected function saveLargeBase64File(
        string $base64Data, 
        string $filePath, 
        int $chunkSize = 2097152
    ): bool {
        $inputStream = fopen('php://memory', 'r+');
        fwrite($inputStream, $base64Data);
        rewind($inputStream);

        $outputStream = fopen($filePath, 'w');
        if ($outputStream === false) {
            fclose($inputStream);
            return false;
        }

        // Set stream buffer to 0 for direct writing
        stream_set_write_buffer($outputStream, 0);

        // Decode and write in chunks
        $buffer = '';
        $decodedChunk = '';
        
        while (!feof($inputStream)) {
            $buffer .= fread($inputStream, $chunkSize);
            
            // Process complete base64 chunks (base64 length must be multiple of 4)
            $chunkLength = strlen($buffer);
            $processLength = $chunkLength - ($chunkLength % 4);
            
            if ($processLength > 0) {
                $decodedChunk = base64_decode(substr($buffer, 0, $processLength), true);
                if ($decodedChunk === false) {
                    fclose($inputStream);
                    fclose($outputStream);
                    unlink($filePath);
                    return false;
                }
                
                fwrite($outputStream, $decodedChunk);
                $buffer = substr($buffer, $processLength);
            }
        }

        // Process remaining buffer
        if (!empty($buffer)) {
            $decodedChunk = base64_decode($buffer, true);
            if ($decodedChunk !== false) {
                fwrite($outputStream, $decodedChunk);
            }
        }

        fclose($inputStream);
        fclose($outputStream);

        return true;
    }

    /**
     * Convert MIME type to file extension
     */
    protected function mimeToExtension(string $mimeType): string
    {
        $mimeMap = [
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'video/ogg' => 'ogv',
            'video/webm' => 'webm',
            'video/avi' => 'avi',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
            'video/x-matroska' => 'mkv',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/ogg' => 'oga',
            'audio/webm' => 'weba',
            'audio/aac' => 'aac',
            'audio/x-wav' => 'wav',
            'audio/flac' => 'flac',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/x-7z-compressed' => '7z',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
        ];

        return $mimeMap[$mimeType] ?? 'bin';
    }

    /**
     * Decode base64 image and get image info
     */
    public function decodeBase64Image(string $base64): array
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $data = substr($base64, strpos($base64, ',') + 1);
            $extension = strtolower($type[1]);
            $binary = base64_decode($data, true);
            
            if ($binary === false) {
                throw new InvalidArgumentException('Invalid base64 data');
            }

            // Get image size
            $imageInfo = getimagesizefromstring($binary);
            
            return [
                'binary' => $binary,
                'extension' => $extension,
                'mime_type' => $imageInfo['mime'] ?? 'image/' . $extension,
                'width' => $imageInfo[0] ?? 0,
                'height' => $imageInfo[1] ?? 0,
                'size' => strlen($binary),
            ];
        }

        throw new InvalidArgumentException('Invalid base64 image format');
    }

    /**
     * Validate if base64 string is a valid image
     */
    public function isValidImage(string $base64): bool
    {
        try {
            $this->decodeBase64Image($base64);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get image dimensions from base64
     */
    public function getImageDimensions(string $base64): array
    {
        $info = $this->decodeBase64Image($base64);
        return [
            'width' => $info['width'],
            'height' => $info['height'],
            'ratio' => $info['height'] > 0 ? $info['width'] / $info['height'] : 0,
        ];
    }

    /**
     * Get supported image formats
     */
    public function getSupportedFormats(): array
    {
        return ['webp', 'jpg', 'jpeg', 'png', 'gif', 'svg'];
    }

    /**
     * Check if format is supported
     */
    public function isFormatSupported(string $format): bool
    {
        return in_array(strtolower($format), $this->getSupportedFormats());
    }

    /**
     * Generate unique filename with extension
     */
    public function generateFilename(?string $extension = null, ?string $prefix = null): string
    {
        $extension = $extension ?: $this->config['default_format'];
        $prefix = $prefix ? $prefix . '_' : '';
        
        return $prefix . uniqid() . '.' . $extension;
    }

    /**
     * Get public URL for stored file
     */
    public function getPublicUrl(string $path): string
    {
        $disk = $this->config['disk'];
        
        if ($disk === 'public') {
            return asset('storage/' . $path);
        }
        
        // For other disks, you might need to use the storage facade
        return \Storage::disk($disk)->url($path);
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(string $path): bool
    {
        $disk = $this->config['disk'];
        return \Storage::disk($disk)->delete($path);
    }

    /**
     * Check if file exists in storage
     */
    public function fileExists(string $path): bool
    {
        $disk = $this->config['disk'];
        return \Storage::disk($disk)->exists($path);
    }

    /**
     * Get file size from storage
     */
    public function getFileSize(string $path): ?int
    {
        $disk = $this->config['disk'];
        return \Storage::disk($disk)->size($path);
    }

    /**
     * Get file MIME type from storage
     */
    public function getFileMimeType(string $path): ?string
    {
        $disk = $this->config['disk'];
        return \Storage::disk($disk)->mimeType($path);
    }

    /**
     * Get all files in a folder
     */
    public function getFilesInFolder(string $folder): array
    {
        $disk = $this->config['disk'];
        return \Storage::disk($disk)->files($folder);
    }

    /**
     * Create folder if it doesn't exist
     */
    public function ensureFolderExists(string $folder): bool
    {
        $disk = $this->config['disk'];
        $fullPath = storage_path('app/' . $disk . '/' . $folder);
        
        if (!is_dir($fullPath)) {
            return mkdir($fullPath, 0755, true);
        }
        
        return true;
    }

    /**
     * Helper function for image-cropper.js
     */
    public function getJsConfig(): array
    {
        return [
            'client_side_only' => $this->isClientSideOnly(),
            'upload_url' => $this->getUploadRoute(),
            'ratios' => $this->getDefaultRatios(),
            'auto_process_forms' => $this->shouldAutoProcessForms(),
            'max_file_size' => $this->config['max_file_size'] ?? 10,
            'allowed_types' => $this->config['allowed_types'] ?? [],
        ];
    }
}