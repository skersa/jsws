(function () {
            var t = localStorage.getItem('theme') || 'dark';
            var d = document.documentElement;
            d.classList.remove('light-mode', 'dark-mode');
            d.classList.add(t + '-mode');
            d.setAttribute('data-bs-theme', t === 'dark' ? 'dark' : 'light');
        })();

(function initSelect2() {
            if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.select2) {
                return setTimeout(initSelect2, 120);
            }
            var $ = window.jQuery;
            $('.js-select2').each(function () {
                var $el = $(this);
                if ($el.data('select2')) return;
                $el.select2({
                    width: '100%',
                    dropdownParent: $el.closest('form').length ? $el.closest('form') : $(document.body),
                    placeholder: $el.data('placeholder') || 'Seçiniz...',
                    allowClear: $el.data('allow-clear') ? true : false,
                });
            });
        })();
