@props([
    'name' => 'image',
    'ratios' => [],
    'radio' => true,
    'crop' => true,
    'uploadUrl' => url('/image-upload'),
    'id' => null,
])

@php
    $id = $id ?? 'imgcrop_'.\Illuminate\Support\Str::random(6);
    $ratiosJson = json_encode($ratios ?: config('image-cropper.default_ratios'));
@endphp

<div class="image-cropper-component" data-upload-url="{{ $uploadUrl }}" data-name="{{ $name }}" id="{{ $id }}">
    <input type="file" id="{{ $id }}_input" accept="image/*" class="form-control" />

    @if($crop)
        <div class="mt-2">
            <img id="{{ $id }}_preview" style="max-width:100%; display:none;"/>
        </div>

        @if($radio)
            <div class="mt-2" id="{{ $id }}_ratios"></div>
        @endif

        <div class="mt-2">
            <select id="{{ $id }}_canvas_size" class="form-control">
                <option value="default">Original Size</option>
                <option value="300x300">300 × 300</option>
                <option value="600x800">600 × 800</option>
                <option value="800x800">800 × 800</option>
            </select>
        </div>

        <div class="mt-2">
            <button type="button" id="{{ $id }}_upload_btn" class="btn btn-primary">Upload Cropped Image</button>
        </div>
    @else
        <div class="mt-2">
            <button type="button" id="{{ $id }}_direct_upload_btn" class="btn btn-success">Upload Image</button>
        </div>
    @endif

    <input type="hidden" name="{{ $name }}" id="{{ $id }}_result" />
</div>

{{-- @push('styles') --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
{{-- @endpush

@push('scripts') --}}
<script>
    window.__ImageCropper_DEFAULTS = window.__ImageCropper_DEFAULTS || {};
    window.__ImageCropper_DEFAULTS["{{ $id }}"] = {
        id: "{{ $id }}",
        name: "{{ $name }}",
        ratios: {!! $ratiosJson !!},
        crop: {{ $crop ? 'true' : 'false' }},
        radio: {{ $radio ? 'true' : 'false' }},
        uploadUrl: "{{ $uploadUrl }}"
    };
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="{{ asset('vendor/image-cropper/js/auto-transform.js') }}"></script>
{{-- @endpush --}}
