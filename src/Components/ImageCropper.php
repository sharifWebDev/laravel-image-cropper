<?php

namespace Sharifuddin\ImageCropper\View\Components;

use Illuminate\View\Component;

class ImageCropper extends Component
{
    public $name;
    public $ratios;
    public $radio;
    public $crop;

    public function __construct(
        string $name,
        array $ratios = null,
        bool $radio = null,
        bool $crop = null
    ) {
        $this->name = $name;
        $this->ratios = $ratios ?? config('image-cropper.default_ratios');
        $this->radio = $radio ?? config('image-cropper.enable_ratio', true);
        $this->crop = $crop ?? config('image-cropper.enable_crop', true);
    }

    public function render()
    {
        return view('image-cropper::component');
    }
}
