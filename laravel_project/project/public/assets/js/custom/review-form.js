(function () {
    const wrap = document.getElementById('rvOrderStars');
    const inp  = document.getElementById('rvOrderRating');
    if (!wrap || !inp) return;
    const stars = [...wrap.querySelectorAll('i')];
    const paint = (val) => stars.forEach(s => {
        const on = parseInt(s.dataset.val, 10) <= val;
        s.classList.toggle('bi-star-fill', on);
        s.classList.toggle('bi-star', !on);
    });
    stars.forEach(s => {
        s.addEventListener('mouseenter', () => paint(parseInt(s.dataset.val, 10)));
        s.addEventListener('click', () => { inp.value = s.dataset.val; paint(parseInt(s.dataset.val, 10)); });
    });
    wrap.addEventListener('mouseleave', () => paint(parseInt(inp.value, 10)));
})();
