
let uploadedImages = [];
let currentIndex = 0;

document.getElementById('fileInput').addEventListener('change', function (event) {
    const files = event.target.files;
    uploadedImages = [];
    currentIndex = 0;

    const previewImg = document.getElementById('imagePreview');
    const previewText = document.getElementById('previewText');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const counter = document.getElementById('imageCounter');

    if (files.length > 0) {
        previewText.style.display = 'none';
        previewImg.style.display = 'block';

        let loaded = 0;
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function (e) {
                uploadedImages[i] = e.target.result;
                loaded++;
                if (loaded === files.length) {
                    updatePreviewDisplay();
                }
            };
            reader.readAsDataURL(files[i]);
        }
    } else {
        previewImg.src = "";
        previewImg.style.display = 'none';
        previewText.style.display = 'block';
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
        counter.style.display = 'none';
    }
});

function changeImage(step) {
    currentIndex += step;
    if (currentIndex < 0) currentIndex = uploadedImages.length - 1;
    if (currentIndex >= uploadedImages.length) currentIndex = 0;
    updatePreviewDisplay();
}

function updatePreviewDisplay() {
    const previewImg = document.getElementById('imagePreview');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const counter = document.getElementById('imageCounter');

    if (uploadedImages.length > 0) {
        previewImg.src = uploadedImages[currentIndex];

        if (uploadedImages.length > 1) {
            prevBtn.style.display = 'block';
            nextBtn.style.display = 'block';
            counter.style.display = 'block';
            counter.innerText = (currentIndex + 1) + " / " + uploadedImages.length;
        } else {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            counter.style.display = 'none';
        }
    }
}
