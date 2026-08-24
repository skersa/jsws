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
const logoutForm = useForm({});
function logout() { logoutForm.post(route('logout')); }
</script>

<template>
    <Head title="Onay Bekliyor" />
    <div class="auth-form w-100 text-center">
        <div class="mb-10">
            <div class="auth-header text-center mb-8">
                <img src="/assets/media/logos/logo-light.svg" class="logo-light auth-logo" alt="Artirdim">
                <img src="/assets/media/logos/logo-dark.svg" class="logo-dark auth-logo" alt="Artirdim">
            </div>
            <h1 class="fw-bold fs-2 mb-3">Hesabın henüz onaylanmadı</h1>
            <p class="text-muted fs-6">Admin ekibimiz hesabını inceliyor. Onaylandıktan sonra sisteme giriş yapabilirsin.</p>
        </div>

        <div class="alert alert-info text-start mb-6">
            <div class="fw-bold mb-2">Neden bunu görüyorsun?</div>
            <ul class="mb-0">
                <li>Hesap güvenlik kontrolünden geçiyor</li>
                <li>Satıcı/Alıcı doğrulaması yapılıyor</li>
                <li>Spam ve sahte hesapları engellemek için</li>
            </ul>
        </div>

        <div class="d-grid mb-4">
            <a href="mailto:support@site.com" class="btn btn-auth-primary btn-lg">Destek ile iletişime geç</a>
        </div>
        <form @submit.prevent="logout">
            <div class="d-grid">
                <button class="btn btn-auth-outline btn-lg" data-testid="pending-logout">Çıkış Yap</button>
            </div>
        </form>
    </div>
</template>
