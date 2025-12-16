@props([
    'name' => 'image',
    'ratios' => [],
    'radio' => true,
    'crop' => true,
    'uploadUrl' => null,
    'id' => null,
])

@php
    $config = app('image-cropper')->config();
    
    $id = $id ?? 'imgcrop_' . \Illuminate\Support\Str::random(8);
    $ratios = $ratios ?: $config['default_ratios'];
    $radio = $radio ?? $config['enable_ratio'];
    $crop = $crop ?? $config['enable_crop'];
    $uploadUrl = $uploadUrl ?? url($config['upload_route']);
    
    $ratiosJson = json_encode($ratios);
@endphp

<div class="image-cropper-wrapper" id="{{ $id }}_wrapper">
    <div class="image-cropper-component"
         data-upload-url="{{ $uploadUrl }}"
         data-name="{{ $name }}"
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
                {{ $attributes->get('help', 'Select an image to crop. Supported formats: JPG, PNG, WebP.') }}
            </div>
        </div>

        <!-- Preview Container -->
        @if($crop)
            <div class="mb-3">
                <img id="{{ $id }}_preview"
                     style="max-width: 100%; max-height: 400px; display: none;"
                     alt="Image preview" />
            </div>

            <!-- Ratio Selector -->
            @if($radio)
                <div class="mb-3">
                    <label class="form-label">Aspect Ratio</label>
                    <div class="ratio-selector" id="{{ $id }}_ratios">
                        <!-- Ratios will be populated by JavaScript -->
                    </div>
                </div>
            @endif

            <!-- Canvas Size Selector -->
            <div class="mb-3">
                <label for="{{ $id }}_canvas_size" class="form-label">Output Size</label>
                <select id="{{ $id }}_canvas_size" class="form-select">
                    <option value="default">Original Size</option>
                    <option value="300x300">300 × 300</option>
                    <option value="600x600">600 × 600</option>
                    <option value="800x800">800 × 800</option>
                    <option value="1200x1200">1200 × 1200</option>
                </select>
            </div>

            <!-- Upload Button -->
            <div class="mb-3">
                <button type="button"
                        id="{{ $id }}_upload_btn"
                        class="btn btn-primary"
                        style="display: none;">
                    <i class="fas fa-crop-alt"></i> Upload Cropped Image
                </button>
            </div>
        @else
            <!-- Direct Upload Button -->
            <div class="mb-3">
                <button type="button"
                        id="{{ $id }}_direct_upload_btn"
                        class="btn btn-success">
                    <i class="fas fa-upload"></i> Upload Image
                </button>
            </div>
        @endif

        <!-- Hidden Input for Form Submission -->
        <input type="hidden"
               name="{{ $name }}"
               id="{{ $id }}_result"
               value="{{ $attributes->get('value', '') }}" />

        <!-- Status Message -->
        <div id="{{ $id }}_status" class="alert alert-info" style="display: none;"></div>
    </div>
</div>

<!-- Styles -->
<style>
.image-cropper-wrapper {
    position: relative;
    margin: 15px 0;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    background: #f8f9fa;
}

.ratio-selector {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ratio-selector label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    padding: 5px 10px;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: white;
    transition: all 0.2s;
}

.ratio-selector label:hover {
    border-color: #0d6efd;
}

.ratio-selector input[type="radio"]:checked + span {
    color: #0d6efd;
    font-weight: bold;
}
</style>

@push('scripts')
<script>
// Store component configuration
window.imageCropperConfigs = window.imageCropperConfigs || {};
window.imageCropperConfigs['{{ $id }}'] = {
    id: '{{ $id }}',
    name: '{{ $name }}',
    ratios: {!! $ratiosJson !!},
    crop: {{ $crop ? 'true' : 'false' }},
    radio: {{ $radio ? 'true' : 'false' }},
    uploadUrl: '{{ $uploadUrl }}'
};
</script>
@endpush