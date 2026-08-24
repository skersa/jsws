const ta = document.querySelector('textarea[name=body]');
const cc = document.getElementById('char-count');
if (ta && cc) {
    const update = () => { cc.textContent = ta.value.length; };
    ta.addEventListener('input', update);
    update();
}
document.querySelectorAll('[data-faq]').forEach(btn => {
    btn.addEventListener('click', () => {
        const item = btn.closest('.support-faq-item');
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.support-faq-item.open').forEach(el => el.classList.remove('open'));
        if (!isOpen) item.classList.add('open');
    });
});
