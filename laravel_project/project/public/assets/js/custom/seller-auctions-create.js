function previewImages(files) {
    const grid = document.getElementById('previewGrid');
    grid.innerHTML = '';
    Array.from(files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div');
            wrap.className = 'au-preview-item';
            wrap.innerHTML = `<img src="${e.target.result}">
                ${i === 0 ? '<div class="au-preview-cover">Kapak</div>' : ''}`;
            grid.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}
function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('au-dropzone-hover');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
    const input = document.getElementById('images');
    input.files = dt.files;
    previewImages(dt.files);
}
function setEndDate(days) {
    const starts = document.querySelector('[name=starts_at]').value || new Date().toISOString().slice(0,16);
    const d = new Date(starts);
    d.setDate(d.getDate() + days);
    document.querySelector('[name=ends_at]').value = d.toISOString().slice(0,16);
}
