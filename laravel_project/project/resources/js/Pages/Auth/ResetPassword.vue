<script>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { h } from 'vue';
export default {
    layout: (hh, page) => h(AuthLayout, { activeAuctions: page.props.activeAuctions || 0 }, () => page),
};
</script>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
const props = defineProps({ email: String, token: String, activeAuctions: Number });

const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });
function submit() {
    form.post(route('password.store'), { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <Head title="Şifre Sıfırlama" />
    <form class="form w-100" @submit.prevent="submit">
        <input type="hidden" v-model="form.token">
        <div class="text-center mb-10">
            <h1 class="text-muted fw-bolder mb-3">Yeni Şifre Oluştur</h1>
            <div class="text-gray-500 fw-semibold fs-6">Hesabın için güçlü bir şifre belirle</div>
        </div>

        <div class="form-floating mb-4">
            <input type="email" v-model="form.email" class="form-control auth-input" :class="{ 'is-invalid': form.errors.email }" placeholder="E-Posta" data-testid="reset-email">
            <label>E-posta adresi</label>
            <div v-if="form.errors.email" class="invalid-feedback d-block text-danger small">{{ form.errors.email }}</div>
        </div>

        <div class="form-floating mb-4 position-relative" data-kt-password-meter="true">
            <input type="password" v-model="form.password" class="form-control pe-10" :class="{ 'is-invalid': form.errors.password }" placeholder="Şifre" data-testid="reset-password">
            <label>Şifre</label>
            <div v-if="form.errors.password" class="invalid-feedback d-block text-danger small">{{ form.errors.password }}</div>
            <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0" data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-slash fs-2"></i><i class="bi bi-eye fs-2 d-none"></i>
            </span>
        </div>

        <div class="form-floating mb-4 position-relative" data-kt-password-meter="true">
            <input type="password" v-model="form.password_confirmation" class="form-control pe-10" placeholder="Şifre tekrar" data-testid="reset-password-confirm">
            <label>Şifre Tekrar</label>
            <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0" data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-slash fs-2"></i><i class="bi bi-eye fs-2 d-none"></i>
            </span>
        </div>

        <div class="d-grid mb-4">
            <button class="btn btn-auth-primary btn-lg fw-bold" type="submit" :disabled="form.processing" data-testid="reset-submit">Şifreyi Güncelle</button>
        </div>
        <div class="d-grid">
            <Link :href="route('login')" class="btn btn-auth-outline btn-lg">← Geri Dön</Link>
        </div>
    </form>
</template>
