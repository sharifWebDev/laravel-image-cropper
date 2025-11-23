document.querySelectorAll('.image-cropper-input').forEach(input => {
    input.addEventListener('change', function(e){
        const file = e.target.files[0];
        if(!file) return;

        const reader = new FileReader();
        reader.onload = function(evt){
            console.log('Base64 Image:', evt.target.result);
            // Here you can integrate Cropper.js or any cropping library
            
        }
        reader.readAsDataURL(file);
    });
});
