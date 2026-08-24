function _toggleStreamMode() {
    var isVideo = document.querySelector('input[name="stream_mode"][value="video"]').checked;
    document.getElementById('videoUrlField').style.display = isVideo ? '' : 'none';
    document.getElementById('opt-live').classList.toggle('active', !isVideo);
    document.getElementById('opt-video').classList.toggle('active', isVideo);
    var goLive = document.getElementById('goLiveBtn');
    var hint   = document.getElementById('liveDisabledHint');
    if (goLive) { goLive.style.opacity = isVideo ? '.5' : ''; goLive.style.pointerEvents = isVideo ? 'none' : ''; }
    if (hint)   { hint.style.display = isVideo ? '' : 'none'; }
}
function switchImg(el, src) {
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.thumb-img').forEach(t => {
        t.style.opacity = '.6';
        t.style.borderColor = 'transparent';
    });
    el.style.opacity = '1';
    el.style.borderColor = 'var(--primary)';
}