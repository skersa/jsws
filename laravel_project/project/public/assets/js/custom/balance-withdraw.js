function setWithdraw(v, el) {
        document.getElementById('withdrawAmount').value = v;
        document.querySelectorAll('.btn-preset').forEach(b => b.classList.remove('active'));
        if (el) el.classList.add('active');
    }
