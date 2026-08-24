<script setup>
import { onMounted, onUnmounted } from 'vue';

defineProps({
    activeAuctions: { type: Number, default: 0 },
});

onMounted(() => document.body.classList.add('auth-page'));
onUnmounted(() => document.body.classList.remove('auth-page'));
</script>

<template>
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">

            <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 auth-left order-2 order-lg-1">
                <div class="auth-container d-flex flex-center flex-column flex-lg-row-fluid">
                    <div class="w-lg-500px w-100">
                        <slot />
                    </div>
                </div>
            </div>

            <div class="d-flex flex-lg-row-fluid w-lg-50 position-relative order-1 order-lg-2 auth-right" data-testid="auth-right-panel">
                <div class="auth-right-bg"></div>
                <div class="auth-right-overlay"></div>
                <div class="d-flex flex-column justify-content-center align-items-center w-100 position-relative auth-right-content">
                    <div class="auth-brand-badge">
                        <span class="pf-pulse-dot" style="background:#10b981"></span>
                        Şu an <strong>&nbsp;{{ activeAuctions }}&nbsp;</strong> aktif ilan
                    </div>
                    <div class="auth-brand-title">
                        <div class="auth-brand-name">Artirdim</div>
                        <div class="auth-brand-sub">LIVE AUCTION SYSTEM</div>
                    </div>
                    <p class="auth-brand-desc">
                        Gerçek zamanlı müzayedelere katıl, anlık teklif ver ve en iyi fiyatı yakala.
                        <br>Güvenli ve hızlı açık artırma deneyimi.
                    </p>
                    <div class="auth-stat-row">
                        <div class="auth-stat-card">
                            <i class="bi bi-broadcast auth-stat-icon" style="color:#10b981"></i>
                            <div class="auth-stat-num">LIVE</div>
                            <div class="auth-stat-lbl">Aktif Açık Artırma</div>
                        </div>
                        <div class="auth-stat-card">
                            <i class="bi bi-clock-history auth-stat-icon" style="color:var(--primary)"></i>
                            <div class="auth-stat-num">24/7</div>
                            <div class="auth-stat-lbl">Kesintisiz Sistem</div>
                        </div>
                        <div class="auth-stat-card">
                            <i class="bi bi-people-fill auth-stat-icon" style="color:#a78bfa"></i>
                            <div class="auth-stat-num">+1K</div>
                            <div class="auth-stat-lbl">Aktif Kullanıcı</div>
                        </div>
                    </div>
                    <div class="auth-hero-badge">
                        <i class="bi bi-lightning-charge-fill me-2"></i>En hızlı teklif sistemi
                    </div>
                    <div class="auth-hero-features">
                        <div class="auth-hero-feature"><i class="bi bi-shield-check"></i> 256-bit SSL</div>
                        <div class="auth-hero-feature"><i class="bi bi-award"></i> Onaylı Satıcılar</div>
                        <div class="auth-hero-feature"><i class="bi bi-headset"></i> 7/24 Destek</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
