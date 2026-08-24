function copyValue(text, element) {
    navigator.clipboard.writeText(text).then(() => {
        const icon = element.querySelector('i');

        element.classList.add('copied');
        if(icon) icon.className = 'bi bi-check2';

        setTimeout(() => {
            element.classList.remove('copied');
            if(icon) icon.className = 'bi bi-clipboard';
        }, 2000);
    });
}
