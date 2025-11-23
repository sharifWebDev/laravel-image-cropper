Perfect! Here's the updated **README.md snippet** including your **package dependencies, provider, asset publishing, folder permissions, route, controller, and usage examples**. I’ve integrated usage sections for Blade, `<img>`, and server-side upload.

---

# Laravel Image Cropper

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/sharifuddin/laravel-image-cropper)](https://packagist.org/packages/sharifuddin/laravel-image-cropper)
[![Latest Version](https://img.shields.io/packagist/v/sharifuddin/laravel-image-cropper)](https://packagist.org/packages/sharifuddin/laravel-image-cropper)
[![Total Downloads](https://img.shields.io/packagist/dt/sharifuddin/laravel-image-cropper)](https://packagist.org/packages/sharifuddin/laravel-image-cropper)

A professional and flexible Laravel image cropper package. Provides multiple crop ratios, optional cropping, base64 image handling, and optimized image saving in WebP, JPG, PNG, or original formats.

---

## Features

* 🎨 **Multiple crop ratios**: 1:1, 16:9, 4:5, 4:3, 2:3, or custom
* ⚡ **Optional cropping**: Skip cropping and directly upload
* 🔄 **Optional ratio selector**: Show or hide ratio selection
* 🖼️ **Flexible formats**: WebP, JPG, PNG, or original image format
* 🔧 **Custom filenames**: Optionally specify filename
* 🔒 **Optimized uploads**: Compress and save images efficiently
* 💻 **Blade component**: `<x-image-cropper>` with default behaviors
* 🌐 **HTML `<img>` support**: Auto-cropper with JS
* 🧪 **Tested with Laravel 8-12**
* 🛠️ **PSR-4 compliant and helper autoloaded**: Use `saveImage()` globally

---

## Installation

Install the required dependencies:

```bash
composer require sharifuddin/laravel-image-cropper
```

```bash
# Optional optimizer for image compression
composer require intervention/image
composer require spatie/image-optimizer --dev
```

If the service provider is **not auto-discovered**, add it manually in `config/app.php`:

```php
Sharif\ImageCropper\ImageCropperServiceProvider::class,
```

---

## Publish assets & views

```bash
php artisan vendor:publish --provider="Sharif\ImageCropper\ImageCropperServiceProvider" --tag="image-cropper-assets"
php artisan vendor:publish --provider="Sharif\ImageCropper\ImageCropperServiceProvider" --tag="image-cropper-views"
php artisan storage:link
```

Ensure `storage/` and `bootstrap/cache/` are writable:

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo find storage -type d -exec chmod 775 {} \;
sudo find storage -type f -exec chmod 664 {} \;
```

---

## Add upload route & controller

Create `app/Http/Controllers/ImageUploadController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);
        
        // Default WebP with unique filename
        $path = saveImage($request->image);

        //WEBP with unique filename
        $path = saveImage($request->image, 'uploads', 90, 'webp');

        // JPG with unique filename
        $path = saveImage($request->image, 'avatars', 90, 'jpg');

        // PNG with custom filename
        $path = saveImage($request->image, 'avatars', 90, 'png', 'my-avatar');

        // Keep original extension with custom filename
        $path = saveImage($request->image, 'avatars', 90, null, 'original-file');

        return response()->json([
            'success' => true,
            'path' => $path,
        ]);
    }
}
```

Add route in `routes/web.php` or `routes/api.php`:

```php
use App\Http\Controllers\ImageUploadController;

Route::post('/image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
```

---

## Usage

### Blade Component

```blade
{{-- Default (ratios + cropping) --}}
<x-image-cropper name="avatar" />

{{-- Hide ratio selector --}}
<x-image-cropper name="avatar" :radio="false" />

{{-- Disable cropping (direct upload) --}}
<x-image-cropper name="avatar" :crop="false" />

{{-- Override ratios --}}
<x-image-cropper name="avatar" :ratios="[
    '1:1' => 1,
    '16:9' => 16/9,
    '4:5' => 4/5,
    '4:3' => 4/3,
    '2:3' => 2/3
]" />
```

---

### HTML `<img>` Usage

```html
<img class="image-cropper" name="avatar" />

<img class="image-cropper" name="avatar" :radio="false" />

<img class="image-cropper" name="avatar" :crop="false" />

<img class="image-cropper" name="avatar" :ratios='{"1:1":1,"16:9":16/9,"4:5":4/5,"4:3":4/3,"2:3":2/3}' />

```

---

### Server-Side Upload

```php
use Illuminate\Http\Request;

// Default WebP with unique filename
$path = saveImage($request->image);

// JPG with unique filename
$path = saveImage($request->image, 'avatars', 90, 'jpg');

// PNG with custom filename
$path = saveImage($request->image, 'avatars', 90, 'png', 'my-avatar');

// Keep original extension with custom filename
$path = saveImage($request->image, 'avatars', 90, null, 'original-file');
```

---

## ⚙️ Requirements

| Laravel Version | PHP Version | Package Version |
| --------------- | ----------- | --------------- |
| 12.x            | 8.2+        | ^1.0            |
| 11.x            | 8.2+        | ^1.0            |
| 10.x            | 8.1+        | ^1.0            |
| 9.x             | 8.0+        | ^1.0            |
| 8.x             | 8.0+        | ^1.0            |

---

## 🧪 Testing

Run the package tests using PHPUnit:

```bash
vendor/bin/phpunit
```

Run code analysis with PHPStan:

```bash
vendor/bin/phpstan analyse
```

---

## 📜 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author

**Sharif Uddin**

* Email: [sharif.webpro@gmail.com](mailto:sharif.webpro@gmail.com)
* Website: [https://sharifwebdev.github.io/](https://sharifwebdev.github.io/)

---