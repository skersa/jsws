<script>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { h } from 'vue';
export default {
    layout: (hh, page) => h(AuthLayout, { activeAuctions: page.props.activeAuctions || 0 }, () => page),
};
</script>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
defineProps({ activeAuctions: Number });
const form = useForm({ password: '' });
function submit() { form.post(route('password.confirm'), { onFinish: () => form.reset() }); }
</script>

<template>
    <Head title="Şifre Onayla" />
    <form class="form w-100 auth-form" @submit.prevent="submit">
        <div class="text-center mb-10">
            <h1 class="text-muted fw-bolder mb-3">Güvenli Alan</h1>
            <div class="text-gray-500 fw-semibold fs-6">Devam etmeden önce lütfen şifreni onayla.</div>
        </div>
        <div class="form-floating mb-4 position-relative" data-kt-password-meter="true">
            <input type="password" v-model="form.password" class="form-control auth-input pe-5" :class="{ 'is-invalid': form.errors.password }" placeholder="Şifre" autocomplete="current-password" data-testid="confirm-password">
            <label>Şifre</label>
            <div v-if="form.errors.password" class="invalid-feedback d-block text-danger small">{{ form.errors.password }}</div>
            <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0" data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-slash fs-2"></i><i class="bi bi-eye fs-2 d-none"></i>
            </span>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-auth-primary btn-lg" :disabled="form.processing" data-testid="confirm-submit">Onayla</button>
        </div>
    </form>
</template>
