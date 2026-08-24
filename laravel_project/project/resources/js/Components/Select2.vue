<script setup>
/*
 | SPA-güvenli Select2 sarmalayıcı (jQuery + select2, plugins.bundle.js ile global gelir)
 | Blade'deki `.js-select2` init (public/assets/js/custom/app-init.js) ile BİREBİR:
 |   width: 100%, dropdownParent: kapsayan form (yoksa body),
 |   placeholder: data-placeholder, allowClear: data-allow-clear
 | Hiyerarşik kategori seçenekleri (label içinde "— " girintileri) desteklenir.
 | v-model iki yönlü; SPA remount'ta çift-init olmaz, unmount'ta destroy edilir.
*/
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';

const props = defineProps({
    modelValue: { type: [String, Number, null], default: '' },
    options: { type: Array, default: () => [] }, // [{ id/value, label }]
    valueKey: { type: String, default: 'id' },
    labelKey: { type: String, default: 'label' },
    placeholder: { type: String, default: 'Seçiniz...' },
    allowClear: { type: Boolean, default: false },
    selectClass: { type: String, default: 'pf-input' },
    id: { type: String, default: null },
    name: { type: String, default: null },
    testid: { type: String, default: null },
});
const emit = defineEmits(['update:modelValue']);

const selectEl = ref(null);
let $el = null;
let destroyed = false;
const jq = () => window.jQuery;
const norm = (v) => (v === null || v === undefined ? '' : String(v));

function initSelect2() {
    if (destroyed) return;
    const $ = jq();
    if (!$ || !$.fn || !$.fn.select2) { setTimeout(initSelect2, 120); return; }
    if (!selectEl.value) return;
    $el = $(selectEl.value);
    if ($el.data('select2')) return; // çift-init koruması

    $el.select2({
        width: '100%',
        dropdownParent: $el.closest('form').length ? $el.closest('form') : $(document.body),
        placeholder: props.placeholder,
        allowClear: props.allowClear,
    });

    // Başlangıç değerini uygula
    $el.val(norm(props.modelValue)).trigger('change.select2');

    // Kullanıcı seçimi -> v-model
    $el.on('change', () => {
        const v = $el.val();
        emit('update:modelValue', v === null || v === undefined ? '' : v);
    });
}

onMounted(() => nextTick(initSelect2));

// Programatik modelValue değişimi -> select2'yi güncelle
watch(() => props.modelValue, (nv) => {
    if (!$el) return;
    if (norm($el.val()) !== norm(nv)) $el.val(norm(nv)).trigger('change.select2');
});

// Dinamik seçenek değişimi -> select2'yi tazele
watch(() => props.options, async () => {
    if (!$el) return;
    await nextTick();
    $el.val(norm(props.modelValue)).trigger('change.select2');
}, { deep: true });

onBeforeUnmount(() => {
    destroyed = true;
    if ($el) {
        try { $el.off('change'); $el.select2('destroy'); } catch (e) { /* noop */ }
        $el = null;
    }
});
</script>

<template>
    <select
        ref="selectEl"
        :class="selectClass"
        :id="id"
        :name="name"
        :data-testid="testid"
    >
        <option value=""></option>
        <option v-for="o in options" :key="o[valueKey]" :value="o[valueKey]">{{ o[labelKey] }}</option>
    </select>
</template>
