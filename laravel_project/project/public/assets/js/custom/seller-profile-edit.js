function switchTab(key, btn) {
    document.querySelectorAll('.pf-ptab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.s-panel').forEach(p => {
        p.classList.toggle('s-active', p.id === 'tab-' + key);
    });
}

function submitActiveTab() {
    const panel = document.querySelector('.s-panel.s-active');
    if (!panel) return;
    const form = panel.querySelector('form');
    if (form) form.submit();
}

function formatIban(input) {
    let v = input.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
    input.value = v;
}

function previewDoc(input) {
    if (!input.files?.[0]) return;
    const file = input.files[0];
    const label = document.getElementById('docLabel');
    const icon  = document.getElementById('docIcon');
    const zone  = document.getElementById('docUploadZone');
    label.textContent = file.name;
    icon.className = 'bi bi-file-earmark-check';
    icon.style.color = '#10b981';
    zone.style.borderColor = 'rgba(16,185,129,.4)';
}
