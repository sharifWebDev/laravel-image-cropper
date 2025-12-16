<?php

namespace Sharifuddin\ImageCropper\Components;

use Illuminate\View\Component;

class ImageCropper extends Component
{
    public $name;
    public $ratios;
    public $radio;
    public $crop;
    public $uploadUrl;
    public $id;

    public function __construct(
        string $name,
        array $ratios = null,
        bool $radio = null,
        bool $crop = null,
        string $uploadUrl = null,
        string $id = null
    ) {
        $this->name = $name;

        // Get configuration
        $config = app('image-cropper')->config();

        $this->ratios = $ratios ?? $config['default_ratios'];
        $this->radio = $radio ?? $config['enable_ratio'];
        $this->crop = $crop ?? $config['enable_crop'];
        $this->uploadUrl = $uploadUrl ?? url($config['upload_route']);
        $this->id = $id ?? 'imgcrop_' . \Illuminate\Support\Str::random(8);
    }

    public function render()
    {
        return view('image-cropper::component');
    }
}