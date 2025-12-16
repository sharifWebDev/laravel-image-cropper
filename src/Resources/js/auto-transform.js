/**
 * Laravel Image Cropper - Modal Based Cropping (Client-Side Only)
 * Complete auto-transform script for the package
 */

(function() {
    'use strict';

    // Check if Cropper is available
    if (typeof Cropper === 'undefined') {
        console.error('Cropper.js is not loaded. Please include Cropper.js library.');
        return;
    }

    class ImageCropper {
        constructor(config) {
            this.config = config;
            this.cropper = null;
            this.currentFile = null;
            this.modal = null;
            this.originalFile = null;
            this.init();
        }

        init() {
            this.setupElements();
            this.bindEvents();
            this.createModal();
        }

        setupElements() {
            this.container = document.getElementById(this.config.id);
            this.input = document.getElementById(this.config.id + '_input');
            this.result = document.getElementById(this.config.id + '_result');
            this.status = document.getElementById(this.config.id + '_status');
            
            if (!this.input) {
                console.error('Input element not found:', this.config.id + '_input');
                return;
            }
        }

        createModal() {
            const modalId = this.config.id + '_modal';
            
            if (document.getElementById(modalId)) {
                this.modal = new bootstrap.Modal(document.getElementById(modalId));
                this.setupModalEvents();
                this.setupRatios();
                return;
            }

            const modalHTML = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Crop Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="image-cropper-preview-container">
                                            <img id="${this.config.id}_preview" 
                                                 style="max-width: 100%; max-height: 500px;" 
                                                 alt="Image preview" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="cropper-controls">
                                            <div class="mb-3">
                                                <label class="form-label">Aspect Ratio</label>
                                                <div class="ratio-selector" id="${this.config.id}_ratios"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="${this.config.id}_canvas_size" class="form-label">Output Size</label>
                                                <select id="${this.config.id}_canvas_size" class="form-select">
                                                    <option value="default">Original Size</option>
                                                    <option value="300x300">300 × 300</option>
                                                    <option value="600x600">600 × 600</option>
                                                    <option value="800x800">800 × 800</option>
                                                    <option value="1200x1200">1200 × 1200</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Rotation</label>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="${this.config.id}_rotate_left">
                                                        <i class="fas fa-undo"></i> -90°
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="${this.config.id}_rotate_right">
                                                        <i class="fas fa-redo"></i> +90°
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Zoom</label>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="${this.config.id}_zoom_out">
                                                        <i class="fas fa-search-minus"></i> Zoom Out
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="${this.config.id}_zoom_in">
                                                        <i class="fas fa-search-plus"></i> Zoom In
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-success" id="${this.config.id}_crop_btn">
                                                    <i class="fas fa-crop-alt"></i> Crop & Apply
                                                </button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHTML);
            this.modal = new bootstrap.Modal(document.getElementById(modalId));
            
            this.setupModalEvents();
            this.setupRatios();
        }

        setupModalEvents() {
            const modalElement = document.getElementById(this.config.id + '_modal');
            
            const rotateLeft = document.getElementById(this.config.id + '_rotate_left');
            const rotateRight = document.getElementById(this.config.id + '_rotate_right');
            
            if (rotateLeft) rotateLeft.addEventListener('click', () => {
                if (this.cropper) this.cropper.rotate(-90);
            });

            if (rotateRight) rotateRight.addEventListener('click', () => {
                if (this.cropper) this.cropper.rotate(90);
            });

            const zoomOut = document.getElementById(this.config.id + '_zoom_out');
            const zoomIn = document.getElementById(this.config.id + '_zoom_in');
            
            if (zoomOut) zoomOut.addEventListener('click', () => {
                if (this.cropper) this.cropper.zoom(-0.1);
            });

            if (zoomIn) zoomIn.addEventListener('click', () => {
                if (this.cropper) this.cropper.zoom(0.1);
            });

            const cropBtn = document.getElementById(this.config.id + '_crop_btn');
            if (cropBtn) {
                cropBtn.addEventListener('click', () => {
                    this.config.clientSideOnly ? this.handleCropApply() : this.handleCropUpload();
                });
            }

            modalElement.addEventListener('hidden.bs.modal', () => {
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            });
        }

        setupRatios() {
            const ratiosContainer = document.getElementById(this.config.id + '_ratios');
            if (!ratiosContainer) return;

            const ratios = this.config.ratios;
            Object.keys(ratios).forEach((label, index) => {
                const ratio = ratios[label];
                const radioId = `${this.config.id}_ratio_${index}`;

                const labelEl = document.createElement('label');
                labelEl.className = 'ratio-option';
                labelEl.innerHTML = `
                    <input type="radio" name="${this.config.id}_ratio"
                           value="${ratio}"
                           id="${radioId}"
                           ${index === 0 ? 'checked' : ''}>
                    <span>${label}</span>
                `;
                ratiosContainer.appendChild(labelEl);
            });

            ratiosContainer.addEventListener('change', (e) => {
                if (e.target.name === `${this.config.id}_ratio` && this.cropper) {
                    const ratio = this.getSelectedRatio();
                    this.cropper.setAspectRatio(isNaN(ratio) ? NaN : ratio);
                }
            });
        }

        bindEvents() {
            if (this.input) {
                this.input.addEventListener('change', (e) => this.handleFileSelect(e));
            }
        }

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                this.showStatus('Please select a valid image file.', 'error');
                return;
            }

            this.currentFile = file;
            this.originalFile = file;
            const url = URL.createObjectURL(file);
            
            this.openModal(url);
        }

        openModal(imageUrl) {
            this.preview = document.getElementById(this.config.id + '_preview');
            if (!this.preview) {
                console.error('Preview element not found');
                return;
            }
            
            this.preview.src = imageUrl;
            this.modal.show();
            
            const modalElement = document.getElementById(this.config.id + '_modal');
            modalElement.addEventListener('shown.bs.modal', () => {
                this.setupCropper(imageUrl);
            }, { once: true });
        }

        openEditModal() {
            if (this.originalFile) {
                const url = URL.createObjectURL(this.originalFile);
                this.openModal(url);
            } else {
                this.showStatus('Original image not found. Please select a new image.', 'error');
            }
        }

        setupCropper(url) {
            if (typeof Cropper === 'undefined') {
                this.showStatus('Image cropper library not loaded. Please refresh the page.', 'error');
                return;
            }

            if (this.cropper) {
                this.cropper.destroy();
            }

            try {
                this.cropper = new Cropper(this.preview, {
                    aspectRatio: this.getSelectedRatio(),
                    viewMode: 2,
                    autoCropArea: 0.8,
                    movable: true,
                    zoomable: true,
                    rotatable: true,
                    scalable: true,
                    background: false,
                    responsive: true,
                    restore: true,
                    checkCrossOrigin: false,
                    modal: true,
                    guides: true,
                    highlight: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: true
                });
            } catch (error) {
                console.error('Error initializing cropper:', error);
                this.showStatus('Error initializing image cropper: ' + error.message, 'error');
            }
        }

        getSelectedRatio() {
            const selected = document.querySelector(`input[name="${this.config.id}_ratio"]:checked`);
            return selected ? parseFloat(selected.value) : NaN;
        }

        handleCropApply() {
            if (!this.cropper) {
                this.showStatus('Please select an image first.', 'error');
                return;
            }

            this.showStatus('Processing image...', 'info');
            this.setCropButtonDisabled(true);

            try {
                const canvas = this.getCroppedCanvas();
                const base64 = canvas.toDataURL('image/webp', 0.9);

                this.showCroppedImagePreview(base64);
                
                if (this.result) {
                    this.result.value = base64;
                }
                
                this.modal.hide();
                this.showStatus('Image cropped successfully!', 'success');
                
            } catch (error) {
                console.error('Cropping error:', error);
                this.showStatus('Error processing image: ' + error.message, 'error');
            } finally {
                this.setCropButtonDisabled(false);
            }
        }

        async handleCropUpload() {
            if (!this.cropper) {
                this.showStatus('Please select an image first.', 'error');
                return;
            }

            this.showStatus('Processing image...', 'info');
            this.setCropButtonDisabled(true);

            try {
                const canvas = this.getCroppedCanvas();
                const base64 = canvas.toDataURL('image/webp', 0.9);

                const response = await fetch(this.config.uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ image: base64 })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.result.value = data.path;
                    this.showCroppedImagePreview(base64);
                    this.modal.hide();
                    this.showStatus('Image uploaded successfully!', 'success');
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            } catch (error) {
                console.error('Upload error:', error);
                this.showStatus('Upload failed: ' + error.message, 'error');
            } finally {
                this.setCropButtonDisabled(false);
            }
        }

        getCroppedCanvas() {
            let width, height;

            const canvasSize = document.getElementById(this.config.id + '_canvas_size');
            if (canvasSize && canvasSize.value !== 'default') {
                const [w, h] = canvasSize.value.split('x').map(Number);
                width = w;
                height = h;
            }

            return this.cropper.getCroppedCanvas({
                width: width,
                height: height,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
                fillColor: '#fff'
            });
        }

        showCroppedImagePreview(base64Data) {
            this.removeExistingPreview();
            
            const previewContainer = document.createElement('div');
            previewContainer.className = 'cropped-image-preview mt-3';
            previewContainer.innerHTML = `
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Cropped Image Preview</h6>
                        <div>
                            <button type="button" class="btn btn-warning btn-sm edit-crop me-2">
                                <i class="fas fa-edit"></i> Edit Crop
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-preview">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <img src="${base64Data}" 
                             alt="Cropped image preview" 
                             class="img-fluid rounded"
                             style="max-height: 200px;">
                        <div class="mt-2">
                            <small class="text-muted">This cropped image will be submitted with the form</small>
                        </div>
                    </div>
                </div>
            `;

            const removeBtn = previewContainer.querySelector('.remove-preview');
            removeBtn.addEventListener('click', () => {
                this.removeCroppedImage();
            });

            const editBtn = previewContainer.querySelector('.edit-crop');
            editBtn.addEventListener('click', () => {
                this.openEditModal();
            });

            const inputContainer = this.input.closest('.global-image-cropper-container') || this.input.parentNode;
            inputContainer.appendChild(previewContainer);
        }

        removeExistingPreview() {
            const existingPreview = this.container?.querySelector('.cropped-image-preview') || 
                                  this.input.closest('.global-image-cropper-container')?.querySelector('.cropped-image-preview') ||
                                  this.input.parentNode.querySelector('.cropped-image-preview');
            
            if (existingPreview) {
                existingPreview.remove();
            }
        }

        removeCroppedImage() {
            this.removeExistingPreview();
            
            if (this.result) this.result.value = '';
            if (this.input) this.input.value = '';
            
            this.currentFile = null;
            this.originalFile = null;
            
            if (this.preview && this.preview.src && this.preview.src.startsWith('blob:')) {
                URL.revokeObjectURL(this.preview.src);
            }
            
            this.showStatus('Image removed. You can select a new image.', 'info');
        }

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }

        showStatus(message, type = 'info') {
            if (!this.status) return;

            this.status.textContent = message;
            this.status.className = `alert alert-${type}`;
            this.status.style.display = 'block';

            if (type === 'success') {
                setTimeout(() => {
                    this.status.style.display = 'none';
                }, 3000);
            }
        }

        setCropButtonDisabled(disabled) {
            const cropBtn = document.getElementById(this.config.id + '_crop_btn');
            if (cropBtn) {
                cropBtn.disabled = disabled;
                cropBtn.innerHTML = disabled ?
                    '<i class="fas fa-spinner fa-spin"></i> Processing...' :
                    '<i class="fas fa-crop-alt"></i> Crop & Apply';
            }
        }

        destroy() {
            if (this.cropper) this.cropper.destroy();
            if (this.preview && this.preview.src && this.preview.src.startsWith('blob:')) {
                URL.revokeObjectURL(this.preview.src);
            }
            if (this.modal) this.modal.dispose();
        }
    }

    class GlobalImageCropper {
        constructor() {
            this.instances = new Map();
            this.init();
        }

        init() {
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap is not loaded. Please include Bootstrap 5.');
                return;
            }

            if (typeof Cropper === 'undefined') {
                console.error('Cropper.js is not loaded. Please include Cropper.js library.');
                return;
            }

            this.transformFileInputs();
            this.bindFormSubmissions();
        }

        transformFileInputs() {
            document.querySelectorAll('input.image-cropper[type="file"]').forEach((input) => {
                if (input.hasAttribute('data-cropper-processed')) return;

                const form = input.closest('form');
                const name = input.getAttribute('name') || 'image';
                const id = 'global_cropper_' + Math.random().toString(36).substr(2, 9);

                const container = this.createCropperContainer(id, name, input);
                input.parentNode.insertBefore(container, input);
                
                input.style.display = 'none';
                input.setAttribute('data-cropper-processed', 'true');
                input.setAttribute('data-cropper-id', id);

                this.initializeCropperInstance(id, name, form);
            });
        }

        createCropperContainer(id, name, originalInput) {
            const config = window.imageCropperGlobalConfig || {};
            const clientSideOnly = config.client_side_only !== false;
            
            const container = document.createElement('div');
            container.className = 'global-image-cropper-container';
            container.innerHTML = `
                <div class="image-cropper-component"
                     data-name="${name}"
                     data-client-side-only="${clientSideOnly}"
                     ${!clientSideOnly && config.upload_url ? `data-upload-url="${config.upload_url}"` : ''}
                     id="${id}">

                    <div class="mb-3">
                        <label for="${id}_input" class="form-label">
                            ${originalInput.previousElementSibling?.textContent || 'Select Image'}
                        </label>
                        <input type="file"
                               id="${id}_input"
                               accept="image/*"
                               class="form-control ${originalInput.className}"
                               ${originalInput.required ? 'required' : ''}
                               aria-describedby="${id}_help" />
                        <div id="${id}_help" class="form-text">
                            Click to select an image. A cropping modal will open automatically.
                        </div>
                    </div>

                    <input type="hidden"
                           name="${name}"
                           id="${id}_result"
                           value="" />

                    <div id="${id}_status" class="alert alert-info" style="display: none;"></div>
                </div>
            `;

            return container;
        }

        initializeCropperInstance(id, name, form) {
            const config = {
                id: id,
                name: name,
                ratios: this.getDefaultRatios(),
                crop: true,
                radio: true,
                clientSideOnly: (window.imageCropperGlobalConfig || {}).client_side_only !== false,
                uploadUrl: (window.imageCropperGlobalConfig || {}).upload_url,
                form: form
            };

            this.instances.set(id, config);

            setTimeout(() => {
                new ImageCropper(config);
            }, 100);
        }

        getDefaultRatios() {
            return (window.imageCropperGlobalConfig || {}).ratios || {
                'Free': null,
                'Square 1:1': 1,
                'Landscape 16:9': 16/9,
                'Portrait 9:16': 9/16,
                '4:3': 4/3,
                '3:2': 3/2
            };
        }

        bindFormSubmissions() {
            document.addEventListener('submit', (e) => {
                const form = e.target;
                const cropperInputs = form.querySelectorAll('input[data-cropper-id]');
                
                cropperInputs.forEach(input => {
                    const cropperId = input.getAttribute('data-cropper-id');
                    const resultInput = document.getElementById(cropperId + '_result');
                    
                    if (resultInput && resultInput.value) {
                        input.value = resultInput.value;
                    }
                });
            });
        }

        destroyInstance(id) {
            const instance = this.instances.get(id);
            if (instance) {
                instance.destroy();
                this.instances.delete(id);
            }
        }
    }

    // Initialize components from blade components
    function initializeBladeComponents() {
        const configs = window.imageCropperConfigs || {};

        Object.keys(configs).forEach(id => {
            const config = configs[id];
            if (document.getElementById(config.id)) {
                new ImageCropper(config);
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if ((window.imageCropperGlobalConfig || {}).auto_process_forms !== false) {
                window.globalImageCropper = new GlobalImageCropper();
            }
            initializeBladeComponents();
        });
    } else {
        if ((window.imageCropperGlobalConfig || {}).auto_process_forms !== false) {
            window.globalImageCropper = new GlobalImageCropper();
        }
        initializeBladeComponents();
    }

    // Make available globally
    window.ImageCropper = ImageCropper;
    window.GlobalImageCropper = GlobalImageCropper;
})();