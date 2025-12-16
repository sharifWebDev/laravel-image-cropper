@props([
    'name' => 'image',
    'label' => 'Select Image',
    'help' => 'Select an image to upload. Supported formats: JPG, PNG, WebP.',
    'required' => false,
    'class' => '',
    'id' => null,
])

@php
    $id = $id ?? 'imgcrop_' . \Illuminate\Support\Str::random(8);
@endphp

<div class="image-cropper-component-wrapper {{ $class }}">
    <div class="mb-3">
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
        <input type="file" id="{{ $id }}" name="{{ $name }}" class="form-control image-cropper"
            accept="image/*" {{ $required ? 'required' : '' }} {{ $attributes }}>

        @if ($help)
            <div class="form-text">{{ $help }}</div>
        @endif
    </div>
</div>
