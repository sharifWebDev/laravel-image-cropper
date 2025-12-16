# Laravel Image Cropper

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-9.x%2B-orange)](https://laravel.com)
[![Latest Version](https://img.shields.io/packagist/v/sharifuddin/laravel-image-cropper)](https://packagist.org/packages/sharifuddin/laravel-image-cropper)
[![Total Downloads](https://img.shields.io/packagist/dt/sharifuddin/laravel-image-cropper)](https://packagist.org/packages/sharifuddin/laravel-image-cropper)

A powerful, feature-rich Laravel package for client-side image cropping with modal interface. Transform any file input into a professional cropping tool with advanced features.

---

## ✨ Features

### 🖼️ **Advanced Cropping Interface**
- **Modal-based cropping** with full-screen editing
- **Multiple aspect ratios** (Square, Landscape, Portrait, Free)
- **Real-time preview** with zoom and rotation controls
- **Draw & adjust** crop area with mouse
- **Edit functionality** to modify crops after saving

### ⚡ **Smart Upload Options**
- **Client-side only mode** (no server upload required)
- **Server upload mode** with automatic optimization
- **Base64 support** for direct form submission
- **Multiple formats** (WebP, JPG, PNG, GIF, SVG)
- **Automatic quality optimization**

### 🔌 **Seamless Integration**
- **Auto-transform** any file input with `image-cropper` class
- **Blade component** for easy implementation
- **Form compatible** - works with any existing form
- **Responsive design** for all devices
- **Bootstrap 5 compatible**

### 🛡️ **Professional Features**
- **Large file support** up to 10GB
- **Memory-efficient streaming** for big files
- **Comprehensive error handling**
- **CSRF protection** built-in
- **Validation support**

---

## 📦 Installation

### 1. Install via Composer

```bash
composer require sharifuddin/laravel-image-cropper
```

### 2. Publish Assets & Configuration

```bash
# Publish configuration
php artisan vendor:publish --tag=image-cropper-config

# Publish assets (CSS & JS)
php artisan vendor:publish --tag=image-cropper-assets

# Publish views (optional)
php artisan vendor:publish --tag=image-cropper-views
```

### 3. Include Dependencies in Layout

Add these to your main layout file (`resources/views/layouts/app.blade.php`):

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Required Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/image-cropper/css/cropper.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @yield('content')
    
    <!-- Required JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="{{ asset('vendor/image-cropper/js/auto-transform.js') }}"></script>
</body>
</html>
```

### 4. Configure Storage (Optional)

If using server upload mode, link storage:

```bash
php artisan storage:link
```

---

## ⚙️ Configuration

After publishing configuration, edit `config/image-cropper.php`:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    |
    | This option controls the default storage disk that will be used to store
    | uploaded images. You may set this to any of the disks defined in your
    | config/filesystems.php configuration file.
    */
    'disk' => env('IMAGE_CROPPER_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Default Folder
    |--------------------------------------------------------------------------
    |
    | This is the default folder where uploaded images will be stored.
    */
    'default_folder' => env('IMAGE_CROPPER_FOLDER', 'uploads/images'),

    /*
    |--------------------------------------------------------------------------
    | Default Image Format
    |--------------------------------------------------------------------------
    |
    | This option controls the default format for saved images.
    | Supported: 'webp', 'jpg', 'png', 'original'
    */
    'default_format' => env('IMAGE_CROPPER_FORMAT', 'webp'),

    /*
    |--------------------------------------------------------------------------
    | Default Image Quality
    |--------------------------------------------------------------------------
    |
    | This option controls the default quality for saved images (0-100).
    */
    'default_quality' => env('IMAGE_CROPPER_QUALITY', 90),

    /*
    |--------------------------------------------------------------------------
    | Client-Side Only Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, images are only cropped client-side and sent as base64
    | without server upload. When disabled, images are uploaded to server.
    */
    'client_side_only' => env('IMAGE_CROPPER_CLIENT_SIDE_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Auto-Process Form Inputs
    |--------------------------------------------------------------------------
    |
    | When enabled, automatically processes all form inputs with 
    | class "image-cropper" and transforms them into cropping interfaces.
    */
    'auto_process_forms' => env('IMAGE_CROPPER_AUTO_PROCESS', true),

    /*
    |--------------------------------------------------------------------------
    | Default Aspect Ratios
    |--------------------------------------------------------------------------
    |
    | This option defines the default aspect ratios available for selection.
    | Use 'Free' for free cropping (no fixed ratio).
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
    | This option defines the route for image uploads (when not in client-side mode).
    */
    'upload_route' => env('IMAGE_CROPPER_UPLOAD_ROUTE', '/image-upload'),
];
```

---

## 🚀 Usage Examples

### Method 1: Auto-Transform (Easiest)

Simply add `image-cropper` class to any file input:

```blade
<form method="POST" action="/submit">
    @csrf
    
    <!-- This input will be automatically transformed -->
    <div class="mb-3">
        <label for="profile_image" class="form-label">Profile Image</label>
        <input type="file" 
               name="profile_image" 
               class="form-control image-cropper"
               required>
    </div>
    
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### Method 2: Blade Component (Recommended)

Use the included Blade component for more control:

```blade
<x-image-cropper 
    name="profile_image"
    label="Upload Profile Picture"
    help="Select and crop your profile picture (Max: 5MB)"
    required
    class="mt-3"
/>

<!-- With custom ratios -->
<x-image-cropper 
    name="banner_image"
    label="Banner Image"
    :ratios="[
        'Banner 3:1' => 3/1,
        'Square' => 1,
        'Portrait' => 2/3,
    ]"
    :radio="true"
    :crop="true"
/>
```

### Method 3: Server-Side Upload Controller

If not using client-side mode, create a controller:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sharifuddin\ImageCropper\Facades\ImageCropper;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {
            // Save image with custom settings
            $path = ImageCropper::saveImage(
                base64: $request->input('image'),
                folder: 'profiles',
                quality: 85,
                format: 'webp',
                filename: 'user-' . auth()->id()
            );

            // Get public URL
            $url = ImageCropper::getPublicUrl($path);

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => $url,
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
```

### Method 4: Handling Form Submission (Client-Side Mode)

When using client-side mode, handle the base64 image in your controller:

```php
public function store(Request $request)
{
    $request->validate([
        'profile_image' => 'required|string',
    ]);

    $base64Image = $request->input('profile_image');
    
    // Decode and save manually
    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
        $data = substr($base64Image, strpos($base64Image, ',') + 1);
        $data = base64_decode($data);
        
        // Generate filename
        $filename = 'profile_' . auth()->id() . '_' . time() . '.webp';
        $path = 'profiles/' . $filename;
        
        // Save to storage
        Storage::disk('public')->put($path, $data);
        
        // Save to database
        auth()->user()->update([
            'profile_image' => $path
        ]);
        
        return redirect()->back()->with('success', 'Profile image updated!');
    }
    
    return redirect()->back()->with('error', 'Invalid image format');
}
```

---

## 📁 Advanced Features

### 1. Multiple File Types Support

```php
// Save video (supports up to 10GB)
$videoPath = ImageCropper::saveVideo($base64Video, 'videos', 'my-video', 10);

// Save audio
$audioPath = ImageCropper::saveAudio($base64Audio, 'audio', 'podcast');

// Save any file
$filePath = ImageCropper::saveFile($base64File, 'documents', 'application/pdf', 'document');
```

### 2. Image Processing Functions

```php
// Get image dimensions
$dimensions = ImageCropper::getImageDimensions($base64Image);
// Returns: ['width' => 800, 'height' => 600, 'ratio' => 1.333]

// Check if valid image
$isValid = ImageCropper::isValidImage($base64Image); // true/false

// Generate unique filename
$filename = ImageCropper::generateFilename('jpg', 'profile');

// Check if file exists
$exists = ImageCropper::fileExists('uploads/image.jpg');

// Delete file
$deleted = ImageCropper::deleteFile('uploads/old-image.jpg');
```

### 3. Global JavaScript Configuration

You can customize the behavior globally:

```javascript
// Add this before the auto-transform.js script
window.imageCropperGlobalConfig = {
    client_side_only: true, // or false for server uploads
    upload_url: '/api/image-upload', // required if client_side_only is false
    ratios: {
        'Free': null,
        'Square': 1,
        'Wide 2:1': 2/1,
        'Instagram Post': 1.91/1,
        'Instagram Story': 9/16
    },
    auto_process_forms: true,
    max_file_size: 5, // MB
};
```

### 4. Event Handling

Listen to image upload events:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('imageUploaded', function(e) {
        const { path, url } = e.detail;
        console.log('Image uploaded:', path);
        console.log('Image URL:', url);
        
        // Update UI or show notification
        alert('Image uploaded successfully!');
    });
});
```

---

## 🎨 Custom Styling

You can customize the appearance by overriding the CSS:

```css
/* Override in your app.css */
.global-image-cropper-container {
    border: 2px dashed #4a5568;
    border-radius: 10px;
    background: #f7fafc;
}

.cropped-image-preview .card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.ratio-option {
    background: #edf2f7;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.ratio-option:hover {
    background: #cbd5e0;
    transform: translateY(-2px);
}
```

---

## 🔧 Troubleshooting

### Common Issues & Solutions:

1. **Modal doesn't open**
   - Check if Bootstrap 5 and Cropper.js are loaded
   - Verify console for JavaScript errors

2. **Image not saving**
   - Check storage permissions: `chmod -R 775 storage`
   - Ensure storage is linked: `php artisan storage:link`
   - Check disk configuration in `config/filesystems.php`

3. **Base64 too large for form submission**
   - Increase `post_max_size` and `upload_max_filesize` in php.ini
   - Consider using server upload mode for large images

4. **Cropper.js not found**
   - Ensure Cropper.js CDN is included before auto-transform.js
   - Check network tab for failed resource loading

---

## 🧪 Testing

The package includes comprehensive tests:

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Feature

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage-report
```

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 👨‍💻 Author

**Sharif Uddin**

- GitHub: [@sharifwebdev](https://github.com/sharifwebdev)
- Email: sharif.webpro@gmail.com
- Website: [https://sharifwebdev.github.io/](https://sharifwebdev.github.io/)

---

## 🌟 Support

If you find this package useful, please consider:

- ⭐ Starring the repository
- 🐛 Reporting issues
- 💡 Suggesting features
- 🔧 Submitting pull requests

---

## 📚 Changelog

Detailed changes for each release are documented in the [CHANGELOG.md](CHANGELOG.md).

---

## 🔗 Links

- [Packagist](https://packagist.org/packages/sharifuddin/laravel-image-cropper)
- [GitHub Repository](https://github.com/sharifuddin/laravel-image-cropper)
- [Issue Tracker](https://github.com/sharifuddin/laravel-image-cropper/issues)
- [Documentation](https://github.com/sharifuddin/laravel-image-cropper/wiki)

---

## 🎯 Quick Start Cheat Sheet

```bash
# 1. Install
composer require sharifuddin/laravel-image-cropper

# 2. Publish
php artisan vendor:publish --tag=image-cropper-config
php artisan vendor:publish --tag=image-cropper-assets

# 3. Add to layout
# - Include Bootstrap 5, Cropper.js, Font Awesome
# - Include auto-transform.js

# 4. Use in blade
<input type="file" class="image-cropper" name="image">
# OR
<x-image-cropper name="image" label="Upload Image">

# 5. Handle in controller
$path = ImageCropper::saveImage($request->image);
# OR decode base64 manually if using client-side mode
```

---

**Happy Cropping!** 🖼️✨