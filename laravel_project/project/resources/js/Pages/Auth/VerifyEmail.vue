<script>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { h } from 'vue';
export default {
    layout: (hh, page) => h(AuthLayout, { activeAuctions: page.props.activeAuctions || 0 }, () => page),
};
</script>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
const props = defineProps({ status: String, activeAuctions: Number });

const resendForm = useForm({});
const logoutForm = useForm({});
function resend() { resendForm.post(route('verification.send')); }
function logout() { logoutForm.post(route('logout')); }
</script>

<template>
    <Head title="E-posta Doğrula" />
    <div class="auth-form w-100">
        <div class="text-center mb-10">
            <div class="auth-header text-center mb-8">
                <img src="/assets/media/logos/logo-light.svg" class="logo-light auth-logo" alt="Artirdim">
                <img src="/assets/media/logos/logo-dark.svg" class="logo-dark auth-logo" alt="Artirdim">
            </div>
            <h1 class="fw-bold fs-2 mb-2">E-posta adresini doğrula</h1>
            <p class="text-muted fs-6">Hesabını kullanabilmek için e-posta doğrulaması gerekiyor</p>
        </div>

        <div class="alert alert-info mb-6">Kayıt olurken gönderilen doğrulama linkine tıklaman gerekiyor. Eğer mail gelmediyse tekrar gönderebilirsin.</div>
        <div v-if="status === 'verification-link-sent'" class="alert alert-success mb-6">Yeni doğrulama linki e-posta adresine gönderildi.</div>

        <form @submit.prevent="resend">
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-auth-primary btn-lg" :disabled="resendForm.processing" data-testid="verify-resend">Tekrar Gönder</button>
            </div>
        </form>
        <form @submit.prevent="logout">
            <div class="d-grid">
                <button type="submit" class="btn btn-auth-outline btn-lg" data-testid="verify-logout">Çıkış Yap</button>
            </div>
        </form>
        <div class="text-center mt-6">
            <span class="text-muted fs-7">Mail gelmediyse spam klasörünü kontrol et</span>
        </div>
    </div>
</template>
