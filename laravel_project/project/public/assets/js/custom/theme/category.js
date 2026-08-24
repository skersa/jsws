(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        initParentSelect();
        initSlugAutoFill();
        initDescriptionCounter();
        initImagePreview();
        initFormSubmit();
    }

    window.switchETab = function (key, btn) {
        document.querySelectorAll('.pf-etab').forEach(function (b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');

        document.querySelectorAll('.pf-epanel').forEach(function (p) {
            p.classList.remove('active');
        });
        document.getElementById('ep-' + key)?.classList.add('active');
    };

    function initParentSelect() {
        const $select = window.jQuery && window.jQuery('#parentSelect');
        if (!$select || !$select.length) return;

        $select.select2({
            width: '100%',
            placeholder: '— Ana Kategori —',
            allowClear: true,
            language: {
                noResults: function () { return 'Sonuç bulunamadı'; },
                searching: function () { return 'Aranıyor…'; },
            },
        });

        if ($select.data('has-error')) {
            $select
                .next('.select2-container')
                .find('.select2-selection--single')
                .addClass('select2-has-error');
        }
    }

    function initSlugAutoFill() {
        const nameInput = document.getElementById('catName');
        const slugInput = document.getElementById('catSlug');
        if (!nameInput || !slugInput) return;

        nameInput.addEventListener('input', function () {
            if (slugInput.dataset.manual === '1') return;
            slugInput.value = slugify(this.value);
        });

        slugInput.addEventListener('input', function () {
            this.dataset.manual = this.value ? '1' : '0';
        });
    }

    function slugify(value) {
        const trMap = { ğ: 'g', ü: 'u', ş: 's', ı: 'i', ö: 'o', ç: 'c' };

        return value
            .toLowerCase()
            .replace(/[ğüşıöç]/g, function (ch) { return trMap[ch]; })
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function initDescriptionCounter() {
        const textarea = document.querySelector('textarea[name="description"]');
        const counter = document.getElementById('desc_counter');
        if (!textarea || !counter) return;

        const maxLength = textarea.getAttribute('maxlength') || '1000';

        const update = function () {
            counter.textContent = textarea.value.length + '/' + maxLength;
        };

        textarea.addEventListener('input', update);
        update();
    }

    function initImagePreview() {
        const input = document.getElementById('image');
        const preview = document.getElementById('imgPreview');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function initFormSubmit() {
        const form = document.getElementById('categoryForm');
        const saveBtn = document.getElementById('saveBtn');
        if (!form || !saveBtn) return;

        form.addEventListener('submit', function () {
            saveBtn.disabled = true;
            saveBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor...';
        });
    }
})();
