/**
 * Laravel Image Cropper - Auto Transform
 * Handles both blade components and img tag conversion
 */

(function() {
    'use strict';

    class ImageCropper {
        constructor(config) {
            this.config = config;
            this.cropper = null;
            this.currentFile = null;
            this.init();
        }

        init() {
            this.setupElements();
            this.bindEvents();
            this.setupRatios();
        }

        setupElements() {
            this.container = document.getElementById(this.config.id);
            this.input = document.getElementById(this.config.id + '_input');
            this.preview = document.getElementById(this.config.id + '_preview');
            this.result = document.getElementById(this.config.id + '_result');
            this.ratiosContainer = document.getElementById(this.config.id + '_ratios');
            this.uploadBtn = document.getElementById(this.config.id + '_upload_btn');
            this.directBtn = document.getElementById(this.config.id + '_direct_upload_btn');
            this.canvasSize = document.getElementById(this.config.id + '_canvas_size');
            this.status = document.getElementById(this.config.id + '_status');
        }

        setupRatios() {
            if (!this.config.radio || !this.ratiosContainer) return;

            const ratios = this.config.ratios;
            Object.keys(ratios).forEach((label, index) => {
                const ratio = ratios[label];
                const radioId = `${this.config.id}_ratio_${index}`;

                const labelEl = document.createElement('label');
                labelEl.innerHTML = `
                    <input type="radio" name="${this.config.id}_ratio"
                           value="${ratio}"
                           id="${radioId}"
                           ${index === 0 ? 'checked' : ''}>
                    <span>${label}</span>
                `;
                this.ratiosContainer.appendChild(labelEl);
            });
        }

        bindEvents() {
            if (this.input) {
                this.input.addEventListener('change', (e) => this.handleFileSelect(e));
            }

            if (this.uploadBtn) {
                this.uploadBtn.addEventListener('click', () => this.handleCropUpload());
            }

            if (this.directBtn) {
                this.directBtn.addEventListener('click', () => this.handleDirectUpload());
            }

            if (this.ratiosContainer) {
                this.ratiosContainer.addEventListener('change', (e) => this.handleRatioChange(e));
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
            const url = URL.createObjectURL(file);

            if (this.config.crop) {
                this.setupCropper(url);
            } else if (this.directBtn) {
                this.preview.src = url;
                this.preview.style.display = 'block';
            }
        }

        setupCropper(url) {
            if (this.cropper) {
                this.cropper.destroy();
            }

            this.preview.src = url;
            this.preview.style.display = 'block';

            // Initialize cropper after image loads
            this.preview.onload = () => {
                if (typeof Cropper !== 'undefined') {
                    this.cropper = new Cropper(this.preview, {
                        aspectRatio: this.getSelectedRatio(),
                        viewMode: 1,
                        autoCropArea: 0.8,
                        movable: true,
                        zoomable: true,
                        rotatable: true,
                        scalable: true,
                        ready: () => {
                            if (this.uploadBtn) {
                                this.uploadBtn.style.display = 'inline-block';
                            }
                        }
                    });
                } else {
                    console.error('Cropper.js is not loaded');
                    this.showStatus('Cropper library not loaded. Please refresh.', 'error');
                }
            };
        }

        getSelectedRatio() {
            if (!this.config.radio) return NaN;

            const selected = this.container.querySelector(`input[name="${this.config.id}_ratio"]:checked`);
            return selected ? parseFloat(selected.value) : NaN;
        }

        handleRatioChange(event) {
            if (event.target.name === `${this.config.id}_ratio` && this.cropper) {
                const ratio = this.getSelectedRatio();
                this.cropper.setAspectRatio(isNaN(ratio) ? NaN : ratio);
            }
        }

        async handleCropUpload() {
            if (!this.cropper) {
                this.showStatus('Please select an image first.', 'error');
                return;
            }

            this.showStatus('Processing image...', 'info');
            this.setButtonsDisabled(true);

            try {
                const canvas = this.getCroppedCanvas();
                const base64 = canvas.toDataURL('image/webp', 0.9);

                await this.uploadImage(base64);
            } catch (error) {
                console.error('Cropping error:', error);
                this.showStatus('Error processing image: ' + error.message, 'error');
            } finally {
                this.setButtonsDisabled(false);
            }
        }

        async handleDirectUpload() {
            if (!this.currentFile) {
                this.showStatus('Please select an image first.', 'error');
                return;
            }

            this.showStatus('Uploading image...', 'info');
            this.setButtonsDisabled(true);

            try {
                const base64 = await this.fileToBase64(this.currentFile);
                await this.uploadImage(base64);
            } catch (error) {
                console.error('Upload error:', error);
                this.showStatus('Upload failed: ' + error.message, 'error');
            } finally {
                this.setButtonsDisabled(false);
            }
        }

        getCroppedCanvas() {
            let width, height;

            if (this.canvasSize && this.canvasSize.value !== 'default') {
                const [w, h] = this.canvasSize.value.split('x').map(Number);
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

        fileToBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        async uploadImage(base64Data) {
            const csrfToken = this.getCsrfToken();

            const response = await fetch(this.config.uploadUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ image: base64Data })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                this.result.value = data.path;
                this.showStatus('Image uploaded successfully!', 'success');

                // Dispatch event for external listeners
                this.container.dispatchEvent(new CustomEvent('imageUploaded', {
                    detail: { path: data.path, url: data.url }
                }));
            } else {
                throw new Error(data.message || 'Upload failed');
            }
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

        setButtonsDisabled(disabled) {
            [this.uploadBtn, this.directBtn].forEach(btn => {
                if (btn) {
                    btn.disabled = disabled;
                    btn.innerHTML = disabled ?
                        '<i class="fas fa-spinner fa-spin"></i> Processing...' :
                        btn.getAttribute('data-original-text');
                }
            });
        }

        destroy() {
            if (this.cropper) {
                this.cropper.destroy();
            }
            if (this.preview && this.preview.src) {
                URL.revokeObjectURL(this.preview.src);
            }
        }
    }

    // Auto-initialization for blade components
    function initializeBladeComponents() {
        const configs = window.imageCropperConfigs || {};

        Object.keys(configs).forEach(id => {
            const config = configs[id];
            if (document.getElementById(config.id)) {
                new ImageCropper(config);
            }
        });
    }

    // Auto-transform for img tags
    function transformImgTags() {
        document.querySelectorAll('img.image-cropper').forEach(img => {
            const name = img.getAttribute('name') || 'image';
            const crop = img.getAttribute('crop') !== 'false';
            const radio = img.getAttribute('radio') !== 'false';

            let ratios = null;
            try {
                ratios = JSON.parse(img.getAttribute('ratios') || 'null');
            } catch (e) {
                ratios = null;
            }

            const uploadUrl = img.getAttribute('upload-url') || '/image-upload';
            const id = 'imgcrop_' + Math.random().toString(36).substr(2, 9);

            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div class="image-cropper-component"
                     data-upload-url="${uploadUrl}"
                     data-name="${name}"
                     id="${id}">
                    <!-- Component HTML will be generated by the main component -->
                </div>
            `;

            img.parentNode.replaceChild(wrapper, img);

            // Initialize with config
            window.imageCropperConfigs = window.imageCropperConfigs || {};
            window.imageCropperConfigs[id] = {
                id: id,
                name: name,
                ratios: ratios,
                crop: crop,
                radio: radio,
                uploadUrl: uploadUrl
            };
        });
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        transformImgTags();
        initializeBladeComponents();
    });

    // Make ImageCropper available globally
    window.ImageCropper = ImageCropper;
})();