@props([
    'name' => 'image',
    'ratios' => [],
    'radio' => null,
    'crop' => null,
    'uploadUrl' => null,
    'id' => null,
])

@php
    $config = app('image-cropper')->config();
    
    $id = $id ?? 'imgcrop_' . \Illuminate\Support\Str::random(8);
    $ratios = $ratios ?: $config['default_ratios'];
    $radio = $radio ?? $config['enable_ratio'];
    $crop = $crop ?? $config['enable_crop'];
    
    // Only set upload URL if not in client-side only mode
    $uploadUrl = !$config['client_side_only'] ? ($uploadUrl ?? url($config['upload_route'])) : null;
    
    $ratiosJson = json_encode($ratios);
@endphp

<div class="image-cropper-wrapper" id="{{ $id }}_wrapper">
    <div class="image-cropper-component"
         data-name="{{ $name }}"
         data-client-side-only="{{ $config['client_side_only'] ? 'true' : 'false' }}"
         @if(!$config['client_side_only'])
            data-upload-url="{{ $uploadUrl }}"
         @endif
         id="{{ $id }}">

        <!-- File Input -->
        <div class="mb-3">
            <label for="{{ $id }}_input" class="form-label">{{ $attributes->get('label', 'Select Image') }}</label>
            <input type="file"
                   id="{{ $id }}_input"
                   accept="image/*"
                   class="form-control image-cropper-input"
                   {{ $attributes->except(['label']) }}
                   aria-describedby="{{ $id }}_help" />
            <div id="{{ $id }}_help" class="form-text">
                {{ $attributes->get('help', 'Select an image to crop. Supported formats: JPG, PNG, WebP, GIF.') }}
            </div>
        </div>

        <!-- Hidden Input for Form Submission -->
        <input type="hidden"
               name="{{ $name }}"
               id="{{ $id }}_result"
               value="{{ $attributes->get('value', '') }}" />

        <!-- Status Message -->
        <div id="{{ $id }}_status" class="alert alert-info" style="display: none;"></div>
    </div>
</div>

@if($config['auto_process_forms'])
    <script>
        // Store component configuration
        window.imageCropperConfigs = window.imageCropperConfigs || {};
        window.imageCropperConfigs['{{ $id }}'] = {
            id: '{{ $id }}',
            name: '{{ $name }}',
            ratios: {!! $ratiosJson !!},
            crop: {{ $crop ? 'true' : 'false' }},
            radio: {{ $radio ? 'true' : 'false' }},
            uploadUrl: '{{ $uploadUrl }}',
            clientSideOnly: {{ $config['client_side_only'] ? 'true' : 'false' }}
        };
    </script>
@endif