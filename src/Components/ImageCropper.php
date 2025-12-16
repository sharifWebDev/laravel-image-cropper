<?php

namespace Sharifuddin\ImageCropper\Components;

use Illuminate\View\Component;

class ImageCropper extends Component
{
    public $name;
    public $label;
    public $help;
    public $required;
    public $class;
    public $ratios;
    public $radio;
    public $crop;

    public function __construct(
        string $name = 'image',
        string $label = null,
        string $help = null,
        bool $required = false,
        string $class = '',
        array $ratios = null,
        bool $radio = null,
        bool $crop = null
    ) {
        $this->name = $name;
        $this->label = $label ?? 'Select Image';
        $this->help = $help ?? 'Select an image to upload. Supported formats: JPG, PNG, WebP.';
        $this->required = $required;
        $this->class = $class;

        // Get configuration if needed
        $config = app('image-cropper')->config();
        $this->ratios = $ratios ?? $config['default_ratios'];
        $this->radio = $radio ?? $config['enable_ratio'];
        $this->crop = $crop ?? $config['enable_crop'];
    }

    public function render()
    {
        // Use the complex component view
        return view('image-cropper::component', [
            'name' => $this->name,
            'label' => $this->label,
            'help' => $this->help,
            'class' => $this->class,
            'required' => $this->required,
            'ratios' => $this->ratios,
            'radio' => $this->radio,
            'crop' => $this->crop,
        ]);
    }
}
