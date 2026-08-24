function setAmount(val, btn){
    document.getElementById('amount').value = val;

    document.querySelectorAll('.btn-preset').forEach(b => b.classList.remove('active'));
    if(btn) btn.classList.add('active');
}

document.getElementById('amount').addEventListener('input', function() {
    document.querySelectorAll('.btn-preset').forEach(b => b.classList.remove('active'));
});

function togglePaymentFields(method){
    const isCard = method === 'credit_card';

    document.getElementById('cardFields').style.display   = isCard ? 'block' : 'none';
    document.getElementById('bankFields').style.display   = method === 'bank_transfer' ? 'block' : 'none';
    document.getElementById('paparaFields').style.display = method === 'papara' ? 'block' : 'none';

    // Kart alanındaki inputların required durumunu dinamik olarak değiştiriyoruz
    document.querySelectorAll('#cardFields input').forEach(input => {
        input.required = isCard;
    });

    document.querySelectorAll('.payment-tile-modern').forEach(el=>{
        const isChecked = el.querySelector('input').value === method;
        el.classList.toggle('active', isChecked);
    });
}

function formatCardNumber(input){
    let val = input.value.replace(/\D/g,'').slice(0,16);
    input.value = val.match(/.{1,4}/g)?.join(' ') ?? val;
}

function formatExpiry(input){
    let val = input.value.replace(/\D/g,'').slice(0,4);
    if(val.length>=3) val = val.slice(0,2)+'/'+val.slice(2);
    input.value = val;
}

function copyValue(text, element) {
    navigator.clipboard.writeText(text).then(() => {
        const icon = element.querySelector('i');
        const span = element.querySelector('span');

        element.classList.add('copied');
        if(icon) icon.className = 'bi bi-check-all me-1';
        if(span) span.innerText = 'Kopyalandı!';

        setTimeout(() => {
            element.classList.remove('copied');
            if(icon) icon.className = 'bi bi-clipboard me-1';
            if(span) span.innerText = 'Kopyala';
        }, 2000);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Sayfa ilk yüklendiğinde seçili olan yönteme göre required alanları tetikliyoruz
    const activeMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'credit_card';
    togglePaymentFields(activeMethod);

    const firstError = document.querySelector('.is-invalid-error');
    if (firstError) {
        setTimeout(() => {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus({ preventScroll: true });
        }, 200);
    }
});
