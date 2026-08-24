<script>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { h } from 'vue';
export default {
    layout: (hh, page) => h(AuthLayout, { activeAuctions: page.props.activeAuctions || 0 }, () => page),
};
</script>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
const props = defineProps({ status: String, activeAuctions: Number });

const form = useForm({ email: '' });
function submit() { form.post(route('password.email')); }
</script>

<template>
    <Head title="Şifremi unuttum" />
    <form class="form w-100" @submit.prevent="submit">
        <div class="text-center mb-10">
            <h1 class="text-muted fw-bolder mb-3">Şifrenizi mi unuttunuz?</h1>
            <div class="text-gray-500 fw-semibold fs-6">Şifrenizi sıfırlamak için e-posta adresinizi girin.</div>
        </div>

        <div v-if="status" class="alert alert-success d-flex align-items-center p-5 mb-10">
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-success">İşlem Başarılı.</h4>
                <span>E-posta adresinize şifre yenileme bağlantısı içeren bir posta gönderdik.</span>
            </div>
        </div>

        <div class="form-floating mb-5">
            <input type="email" v-model="form.email" class="form-control auth-input" :class="{ 'is-invalid': form.errors.email }" id="email" placeholder="E-Posta" autocomplete="off" data-testid="forgot-email">
            <label class="text-muted">E-posta adresi</label>
            <div v-if="form.errors.email" class="invalid-feedback d-block text-danger small">{{ form.errors.email }}</div>
        </div>

        <div class="d-grid mb-4">
            <button class="btn btn-auth-primary btn-lg fw-bold" type="submit" :disabled="form.processing" data-testid="forgot-submit">Gönder</button>
        </div>
        <div class="d-grid">
            <Link :href="route('login')" class="btn btn-auth-outline btn-lg">← Geri dön</Link>
        </div>
    </form>
</template>
