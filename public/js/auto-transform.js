/**
 * auto-transform.js
 * Initializes each image-cropper component or converts <img class="image-cropper"> into the component.
 *
 * Requires: Cropper.js loaded on page
 */

(function () {
    // helper: safe JSON eval for ratios attribute on <img>
    function parseRatiosAttr(attr) {
        if (!attr) return null;
        try {
            // try JSON first
            return JSON.parse(attr.replace(/(['"])?([a-zA-Z0-9_]+)(['"])?:/g, '"$2":'));
        } catch (e) {
            try {
                // fallback: eval (use cautiously)
                // eslint-disable-next-line no-eval
                return eval('(' + attr + ')');
            } catch (e2) {
                return null;
            }
        }
    }

    // Convert <img class="image-cropper"> to in-DOM component by injecting the component's HTML (lightweight)
    function transformImgTag(img) {
        const name = img.getAttribute('name') || 'image';
        const cropAttr = img.getAttribute(':crop');
        const radioAttr = img.getAttribute(':radio');
        const ratiosAttr = img.getAttribute(':ratios');

        const crop = cropAttr === null ? true : (cropAttr === 'false' ? false : true);
        const radio = radioAttr === null ? true : (radioAttr === 'false' ? false : true);
        const ratios = parseRatiosAttr(ratiosAttr) || {
            '1:1': 1,
            '16:9': 16/9,
            '4:5': 4/5,
            '4:3': 4/3,
            '2:3': 2/3
        };

        // create wrapper div
        const wrapper = document.createElement('div');
        wrapper.className = 'image-cropper-component-wrapper';

        // minimal markup: file input + preview + hidden input
        const id = 'imgcrop_auto_' + Math.random().toString(36).substr(2, 6);
        wrapper.innerHTML = `
            <div id="${id}" class="image-cropper-component" data-upload-url="/image-upload" data-name="${name}"></div>
        `;

        // insert the wrapper in place of image
        img.parentNode.replaceChild(wrapper, img);

        // inject a dynamic component via the global init function (defined below)
        if (window.__ImageCropper_INIT) {
            window.__ImageCropper_INIT(id, { name, ratios, radio, crop });
        } else {
            // store config for later
            window.__ImageCropper_DEFAULTS = window.__ImageCropper_DEFAULTS || {};
            window.__ImageCropper_DEFAULTS[id] = { id, name, ratios, radio, crop, uploadUrl: '/image-upload' };
        }
    }

    // On DOM ready: transform <img> tags
    document.addEventListener('DOMContentLoaded', function () {
        // convert img tags first
        document.querySelectorAll('img.image-cropper').forEach(transformImgTag);
        // then initialize existing blade components that emitted defaults
        initializeAllComponents();
    });

    // initialization function - will be used to build the UI for each component
    function initializeAllComponents() {
        const defaults = window.__ImageCropper_DEFAULTS || {};
        Object.keys(defaults).forEach(function (id) {
            const cfg = defaults[id];
            initComponent(cfg);
        });
    }

    // core init: build UI inside wrapper element id (cfg.id)
    function initComponent(cfg) {
        const container = document.getElementById(cfg.id);
        if (!container) return;

        const name = cfg.name || 'image';
        const uploadUrl = cfg.uploadUrl || container.dataset.uploadUrl || '/image-upload';
        const crop = cfg.crop !== undefined ? cfg.crop : true;
        const radio = cfg.radio !== undefined ? cfg.radio : true;
        const ratios = cfg.ratios || {
            '1:1': 1,
            '16:9': 16/9,
            '4:5': 4/5,
            '4:3': 4/3,
            '2:3': 2/3
        };

        // build markup
        const inputId = cfg.id + '_input';
        const previewId = cfg.id + '_preview';
        const resultId = cfg.id + '_result';
        const ratiosContainerId = cfg.id + '_ratios';
        const uploadBtnId = cfg.id + '_upload_btn';
        const directBtnId = cfg.id + '_direct_upload_btn';
        const canvasSizeId = cfg.id + '_canvas_size';

        let html = '';
        html += '<input type="file" id="' + inputId + '" accept="image/*" class="form-control"/>';
        if (crop) {
            html += '<div class="mt-2"><img id="' + previewId + '" style="max-width:100%; display:none;"></div>';
            if (radio) {
                html += '<div class="mt-2" id="' + ratiosContainerId + '"></div>';
            }
            html += '<div class="mt-2"><select id="' + canvasSizeId + '" class="form-control"><option value="default">Original Size</option><option value="300x300">300 × 300</option><option value="600x800">600 × 800</option><option value="800x800">800 × 800</option></select></div>';
            html += '<div class="mt-2"><button type="button" id="' + uploadBtnId + '" class="btn btn-primary">Upload Cropped Image</button></div>';
        } else {
            html += '<div class="mt-2"><button type="button" id="' + directBtnId + '" class="btn btn-success">Upload Image</button></div>';
        }
        html += '<input type="hidden" name="' + name + '" id="' + resultId + '"/>';
        container.innerHTML = html;

        // If ratios container exists, populate radio buttons
        if (radio && document.getElementById(ratiosContainerId)) {
            const rc = document.getElementById(ratiosContainerId);
            Object.keys(ratios).forEach(function (label, idx) {
                const val = ratios[label];
                const rid = ratiosContainerId + '_r_' + idx;
                const checked = idx === 0 ? 'checked' : '';
                const el = document.createElement('label');
                el.style.marginRight = '8px';
                el.innerHTML = '<input type="radio" name="' + ratiosContainerId + '_radio" value="' + val + '" ' + checked + '> ' + label;
                rc.appendChild(el);
            });
        }

        // setup Cropper logic
        let cropper = null;
        const fileInput = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const resultInput = document.getElementById(resultId);
        const canvasSizeEl = document.getElementById(canvasSizeId);
        const uploadBtn = document.getElementById(uploadBtnId);
        const directBtn = document.getElementById(directBtnId);

        // helper: get selected ratio
        function getSelectedRatio() {
            if (!radio) return NaN;
            const radios = container.querySelectorAll('input[name="' + ratiosContainerId + '_radio"]');
            for (let r of radios) {
                if (r.checked) return parseFloat(r.value);
            }
            return NaN;
        }

        // file change
        fileInput.addEventListener('change', function (e) {
            const f = e.target.files[0];
            if (!f) return;
            const url = URL.createObjectURL(f);
            if (preview) {
                preview.src = url;
                preview.style.display = 'block';
            }

            // direct upload: store base64 without cropping
            if (!crop && directBtn) {
                const reader = new FileReader();
                reader.onload = function (ev) {
                    const b = ev.target.result;
                    resultInput.value = b;
                    // do upload
                    fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ image: b })
                    }).then(r => r.json()).then(data => {
                        if (data && data.file) alert('Uploaded: ' + data.file);
                    }).catch(()=> alert('Upload error'));
                };
                reader.readAsDataURL(f);
                return;
            }

            // destroy previous cropper
            if (cropper) {
                try { cropper.destroy(); } catch (e) {}
                cropper = null;
            }

            // init cropper after image load
            setTimeout(function () {
                if (!preview) return;
                cropper = new Cropper(preview, {
                    viewMode: 1,
                    autoCropArea: 1,
                    movable: true,
                    zoomable: true,
                });

                // set ratio if radio present
                const r = getSelectedRatio();
                cropper.setAspectRatio(isNaN(r) ? NaN : r);
            }, 100);
        });

        // ratio change listener (delegated)
        if (radio) {
            container.addEventListener('change', function (e) {
                if (e.target && e.target.name === ratiosContainerId + '_radio') {
                    const r = getSelectedRatio();
                    if (cropper) cropper.setAspectRatio(isNaN(r) ? NaN : r);
                }
            });
        }

        // upload cropped
        if (uploadBtn) {
            uploadBtn.addEventListener('click', function () {
                if (!cropper) { alert('No image loaded'); return; }

                let width, height;
                if (canvasSizeEl && canvasSizeEl.value !== 'default') {
                    const parts = canvasSizeEl.value.split('x');
                    width = parseInt(parts[0]); height = parseInt(parts[1]);
                }

                const canvas = cropper.getCroppedCanvas({
                    width: width || undefined,
                    height: height || undefined,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                const base64 = canvas.toDataURL('image/webp', 0.9);
                resultInput.value = base64;

                // send to server
                fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ image: base64 })
                }).then(r => r.json()).then(data => {
                    if (data && data.file) {
                        alert('Uploaded: ' + data.file);
                    } else {
                        alert('Upload failed');
                    }
                }).catch(()=> alert('Upload error'));
            });
        }

        // direct upload button (if any)
        if (directBtn) {
            directBtn.addEventListener('click', function () {
                // trigger the logic same as file change
                if (fileInput.files.length === 0) { alert('Select file first'); return; }
                const evt = new Event('change'); fileInput.dispatchEvent(evt);
            });
        }
    }

    // expose initializer for dynamic transforms
    window.__ImageCropper_INIT = function (id, cfg) {
        window.__ImageCropper_DEFAULTS = window.__ImageCropper_DEFAULTS || {};
        window.__ImageCropper_DEFAULTS[id] = cfg;
        // ensure next tick to init
        setTimeout(function () {
            const cb = window.__ImageCropper_INIT_CALLBACK;
            if (typeof cb === 'function') cb();
            // or just init immediate:
            if (typeof window.__ImageCropper_INIT_INTERNAL === 'function') {
                window.__ImageCropper_INIT_INTERNAL();
            } else {
                // fallback: simple initialize from map
                (function () {
                    const defaults = window.__ImageCropper_DEFAULTS || {};
                    Object.keys(defaults).forEach(function (k) {
                        // if element exists initialize
                        if (document.getElementById(k)) {
                            // rely on container markup created in blade view to call initComponent via this script detection
                        }
                    });
                })();
            }
        }, 50);
    };

    // Attempt to initialize any components that have their DOM and defaults present:
    window.__ImageCropper_INIT_INTERNAL = function () {
        const defaults = window.__ImageCropper_DEFAULTS || {};
        Object.keys(defaults).forEach(function (id) {
            const cfg = defaults[id];
            // If the blade component emitted config and has corresponding DOM wrapper
            try { initComponent(cfg); } catch (e) {}
        });
    };
})();
