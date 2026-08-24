<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { onMounted, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    pf: Object,
    config: Object,
});

const socialRows = [
    { key: 'instagram', icon: 'bi-instagram', prefix: 'instagram.com/' },
    { key: 'twitter', icon: 'bi-twitter-x', prefix: 'x.com/' },
    { key: 'youtube', icon: 'bi-youtube', prefix: 'youtube.com/@' },
    { key: 'linkedin', icon: 'bi-linkedin', prefix: 'linkedin.com/in/' },
];

function err(field) {
    return props.pf.errors_flat && props.pf.errors_flat[field];
}

function messageUser() {
    router.post(props.pf.urls.messages_start, { user_id: props.pf.user.id });
}

async function boot() {
    await nextTick();
    if (window.__profileShowInit) {
        window.__profileShowInit();
    } else {
        const s = document.createElement('script');
        s.src = '/assets/js/custom/profile-show.js';
        document.body.appendChild(s);
    }
    // hikaye kaynaklarını yeniden tara (global story-viewer için)
    if (window.__refreshStorySources) { try { window.__refreshStorySources(); } catch (e) {} }
}

onMounted(boot);
</script>

<template>
    <Head :title="pf.user.name + ' — Profil'" />

    <div class="pf-root">
        <div class="pf-top">
            <div class="pf-cover"></div>

            <div class="pf-identity">
                <div class="pf-avatar-wrap">
                    <div v-if="pf.stories.has" class="story-item pf-avatar-story"
                         data-testid="profile-avatar-story"
                         :data-story-uid="pf.user.id"
                         :data-story-ids="JSON.stringify(pf.stories.ids)"
                         :data-ring-unseen="pf.stories.ring_unseen"
                         :data-ring-seen="pf.stories.ring_seen"
                         :onclick="`openStoryViewer(${pf.user.id})`"
                         title="Hikayeleri gör" style="cursor:pointer;">
                        <div class="story-ring pf-avatar-ring" :style="pf.stories.ring_unseen">
                            <img :src="pf.user.avatar" :alt="pf.user.name" id="heroAvatar">
                        </div>
                    </div>
                    <div v-else class="pf-avatar-outer">
                        <img :src="pf.user.avatar" :alt="pf.user.name" id="heroAvatar" class="pf-avatar-img">
                    </div>
                    <span v-if="pf.user.is_online" class="pf-online-dot"></span>
                </div>

                <div class="pf-identity-right">
                    <div>
                        <div class="pf-uname-row">
                            <span class="pf-uname" id="heroName">{{ pf.user.name }}</span>
                            <span class="pf-role-badge">{{ pf.user.role_label }}</span>
                        </div>
                        <div v-if="pf.user.username" class="pf-handle" id="heroHandle">{{ pf.user.handle }}</div>
                        <div class="pf-bio" id="heroBio">{{ pf.user.bio_display }}</div>
                    </div>
                </div>
            </div>

            <div class="pf-stats-row">
                <div class="pf-stat">
                    <div class="pf-stat-num">{{ pf.auction_count }}</div>
                    <div class="pf-stat-label">İLAN</div>
                </div>
                <div class="pf-stat">
                    <div class="pf-stat-num">{{ pf.bid_count }}</div>
                    <div class="pf-stat-label">TEKLİF</div>
                </div>
                <div class="pf-stat">
                    <Link :href="pf.urls.followers" class="pf-stat-link">
                        <div class="pf-stat-num" id="follower-count">{{ pf.follower_count }}</div>
                        <div class="pf-stat-label">TAKİPÇİ</div>
                    </Link>
                </div>
                <div class="pf-stat">
                    <Link :href="pf.urls.following" class="pf-stat-link">
                        <div class="pf-stat-num">{{ pf.following_count }}</div>
                        <div class="pf-stat-label">TAKİP</div>
                    </Link>
                </div>
                <div class="pf-stat">
                    <div class="pf-stat-num">{{ pf.rating_fmt }}</div>
                    <div class="pf-stat-label">PUAN</div>
                </div>
            </div>

            <div class="pf-action-row">
                <template v-if="pf.is_owner">
                    <button class="pf-btn-edit" id="editToggle" onclick="toggleEdit()">
                        <i class="bi bi-pencil me-1"></i> Profili Düzenle
                    </button>
                    <button class="pf-btn-icon" aria-label="Paylaş">
                        <i class="bi bi-share"></i>
                    </button>
                </template>
                <template v-else>
                    <template v-if="$page.props.auth.user">
                        <button id="follow-btn" :data-url="pf.urls.follow_toggle"
                                class="pf-btn-primary" :class="{ 'pf-btn-following': pf.is_following }">
                            <template v-if="pf.is_following">
                                <i class="bi bi-person-check-fill me-1"></i><span>Takibi Bırak</span>
                            </template>
                            <template v-else>
                                <i class="bi bi-person-plus me-1"></i><span>Takip Et</span>
                            </template>
                        </button>
                        <form @submit.prevent="messageUser" class="pf-msg-form">
                            <button type="submit" class="pf-btn-secondary" data-testid="profile-message-btn">
                                <i class="bi bi-chat-dots me-1"></i> Mesaj
                            </button>
                        </form>
                    </template>
                </template>
            </div>
        </div>

        <!-- Hikaye kaynağı (global story-viewer okur) -->
        <div v-if="pf.stories.has" class="story-source"
             :data-user-id="pf.user.id"
             :data-story-payload="JSON.stringify(pf.stories.payload)"></div>

        <!-- DÜZENLE DRAWER -->
        <div v-if="pf.is_owner" class="pf-edit-drawer" id="editDrawer">
            <div class="pf-edit-tabs">
                <button class="pf-etab active" onclick="switchETab('genel',this)"><i class="bi bi-person me-1"></i> Genel</button>
                <button class="pf-etab" onclick="switchETab('guvenlik',this)"><i class="bi bi-shield me-1"></i> Güvenlik</button>
                <button class="pf-etab" onclick="switchETab('gizlilik',this)"><i class="bi bi-eye-slash me-1"></i> Gizlilik</button>
                <button class="pf-etab" onclick="switchETab('sosyal',this)"><i class="bi bi-link-45deg me-1"></i> Sosyal</button>
            </div>

            <!-- GENEL -->
            <div id="ep-genel" class="pf-epanel active">
                <div v-if="pf.flash.profile_success" class="pf-alert-success">
                    <i class="bi bi-check-circle-fill"></i><span>{{ pf.flash.profile_success }}</span>
                </div>
                <form method="POST" :action="pf.urls.update" enctype="multipart/form-data">
                    <input type="hidden" name="_token" :value="config.csrf">
                    <input type="hidden" name="_method" value="PUT">
                    <div class="pf-avatar-upload-row">
                        <label for="profile_image" class="pf-upload-avatar" style="cursor:pointer;" title="Fotoğraf değiştir">
                            <img :src="pf.user.avatar" :alt="pf.user.name" id="avatarPreviewSmall" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            <input type="file" id="profile_image" name="profile_image" accept=".png,.jpg,.jpeg,.webp" class="d-none">
                        </label>
                        <div>
                            <div class="pf-upload-title">Profil fotoğrafı</div>
                            <div class="pf-upload-desc">PNG, JPG, WEBP · Maks. 2MB</div>
                            <label for="profile_image" class="pf-btn-photo mt-2 d-inline-flex align-items-center gap-1" style="cursor:pointer;">
                                <i class="bi bi-upload"></i> Fotoğraf yükle
                            </label>
                        </div>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label">Ad soyad<span class="pf-req">*</span></label>
                        <input class="pf-input" :class="{ 'is-invalid': err('name') }" type="text" name="name" :value="pf.form.name" placeholder="Ad">
                        <div v-if="err('name')" class="pf-error">{{ err('name') }}</div>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label">Kullanıcı adı <span class="pf-req">*</span></label>
                        <div class="pf-input-pre">
                            <span class="pf-pre-label">@</span>
                            <input type="text" name="username" id="edit_username" :class="{ 'is-invalid': err('username') }" :value="pf.form.username" maxlength="30" placeholder="kullanici_adi">
                        </div>
                        <div class="pf-hint">Sadece harf, rakam, nokta ve alt çizgi · 3–30 karakter</div>
                        <div v-if="err('username')" class="pf-error">{{ err('username') }}</div>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label">GSM numarası</label>
                        <div class="pf-input-pre">
                            <span class="pf-pre-label">+90</span>
                            <input type="tel" name="phone" :value="pf.form.phone" :class="{ 'is-invalid': err('phone') }" maxlength="15" placeholder="5xx xxx xx xx" required>
                        </div>
                        <div v-if="err('phone')" class="pf-error">{{ err('phone') }}</div>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label">Hakkımda</label>
                        <div style="position:relative;">
                            <textarea class="pf-input" name="bio" id="bio_input" rows="3" :class="{ 'is-invalid': err('bio') }" maxlength="300" oninput="bioCount(this)">{{ pf.form.bio }}</textarea>
                            <span id="bio_counter" class="pf-char-cnt">{{ (pf.form.bio || '').length }}/300</span>
                        </div>
                        <div v-if="err('bio')" class="pf-error">{{ err('bio') }}</div>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label">E-posta</label>
                        <input class="pf-input" type="email" :value="pf.user.email" disabled style="opacity:.4;cursor:not-allowed;">
                        <div class="pf-hint">E-postayı değiştirmek için Güvenlik sekmesini kullan.</div>
                    </div>

                    <div class="pf-footer">
                        <span class="pf-save-info" id="saveInfo"><i class="bi bi-clock"></i> Kaydedilmedi</span>
                        <div class="d-flex gap-2">
                            <button type="reset" class="pf-btn-reset">Sıfırla</button>
                            <button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- GÜVENLİK -->
            <div id="ep-guvenlik" class="pf-epanel">
                <div class="pf-sec-item">
                    <div class="pf-sec-icon" style="background:rgba(99,102,241,.12);"><i class="bi bi-envelope" style="color:#818cf8;"></i></div>
                    <div class="pf-sec-info">
                        <div class="pf-sec-title">E-posta adresi</div>
                        <div class="pf-sec-sub">{{ pf.user.email }}</div>
                    </div>
                    <button class="pf-btn-change" onclick="toggleInline('email-form')">Değiştir</button>
                </div>
                <div class="pf-inline-form" id="email-form">
                    <div v-if="pf.flash.email_success" class="pf-alert-success mb-3">
                        <i class="bi bi-check-circle-fill"></i><span>{{ pf.flash.email_success }}</span>
                    </div>
                    <form method="POST" :action="pf.urls.email">
                        <input type="hidden" name="_token" :value="config.csrf">
                        <input type="hidden" name="_method" value="PUT">
                        <div class="pf-two-col mb-3">
                            <div class="pf-field" style="margin-bottom:0;">
                                <label class="pf-label">Yeni e-posta <span class="pf-req">*</span></label>
                                <input class="pf-input" type="email" name="email" :class="{ 'is-invalid': err('email') }" placeholder="yeni@eposta.com" :value="pf.form.email">
                                <div v-if="err('email')" class="pf-error">{{ err('email') }}</div>
                            </div>
                            <div class="pf-field" style="margin-bottom:0;">
                                <label class="pf-label">Mevcut şifre <span class="pf-req">*</span></label>
                                <input class="pf-input" type="password" name="confirmemailpassword" placeholder="••••••••">
                                <div v-if="err('confirmemailpassword')" class="pf-error">{{ err('confirmemailpassword') }}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="pf-btn-save"><i class="bi bi-check me-1"></i> Güncelle</button>
                            <button type="button" class="pf-btn-reset" onclick="toggleInline('email-form')">Vazgeç</button>
                        </div>
                    </form>
                </div>

                <div class="pf-sec-item">
                    <div class="pf-sec-icon" style="background:rgba(245,158,11,.1);"><i class="bi bi-lock" style="color:#fbbf24;"></i></div>
                    <div class="pf-sec-info">
                        <div class="pf-sec-title">Şifre</div>
                        <div class="pf-sec-sub">Son değişim: bilinmiyor</div>
                    </div>
                    <button class="pf-btn-change" onclick="toggleInline('pass-form')">Değiştir</button>
                </div>
                <div class="pf-inline-form" id="pass-form">
                    <div v-if="pf.flash.password_success" class="pf-alert-success mb-3">
                        <i class="bi bi-check-circle-fill"></i><span>{{ pf.flash.password_success }}</span>
                    </div>
                    <form method="POST" :action="pf.urls.password">
                        <input type="hidden" name="_token" :value="config.csrf">
                        <input type="hidden" name="_method" value="PUT">
                        <div class="pf-field">
                            <label class="pf-label">Mevcut şifre <span class="pf-req">*</span></label>
                            <input class="pf-input" type="password" name="currentpassword" :class="{ 'is-invalid': err('currentpassword') }" placeholder="••••••••">
                            <div v-if="err('currentpassword')" class="pf-error">{{ err('currentpassword') }}</div>
                        </div>
                        <div class="pf-two-col">
                            <div class="pf-field" style="margin-bottom:0;">
                                <label class="pf-label">Yeni şifre <span class="pf-req">*</span></label>
                                <input class="pf-input" type="password" name="password" id="new_pass" :class="{ 'is-invalid': err('password') }" placeholder="••••••••" oninput="passStrength(this)">
                                <div class="pf-pass-bars">
                                    <div class="pf-pbar" id="pb1"></div>
                                    <div class="pf-pbar" id="pb2"></div>
                                    <div class="pf-pbar" id="pb3"></div>
                                    <div class="pf-pbar" id="pb4"></div>
                                </div>
                                <div v-if="err('password')" class="pf-error">{{ err('password') }}</div>
                            </div>
                            <div class="pf-field" style="margin-bottom:0;">
                                <label class="pf-label">Tekrar <span class="pf-req">*</span></label>
                                <input class="pf-input" type="password" name="password_confirmation" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="pf-hint mt-2 mb-3">En az 8 karakter, büyük/küçük harf ve sembol içermeli.</div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="pf-btn-save"><i class="bi bi-check me-1"></i> Güncelle</button>
                            <button type="button" class="pf-btn-reset" onclick="toggleInline('pass-form')">Vazgeç</button>
                        </div>
                    </form>
                </div>

                <div class="pf-sec-item" style="border-bottom:none;">
                    <div class="pf-sec-icon" style="background:rgba(239,68,68,.1);"><i class="bi bi-exclamation-triangle" style="color:#f87171;"></i></div>
                    <div class="pf-sec-info">
                        <div class="pf-sec-title" style="color:#f87171;">Hesabı sil</div>
                        <div class="pf-sec-sub">Tüm veriler kalıcı olarak silinir</div>
                    </div>
                    <button type="button" class="pf-btn-change" style="border-color:rgba(239,68,68,.3);color:#f87171;" onclick="toggleInline('delete-form')" data-testid="account-delete-toggle">Sil</button>
                </div>
                <div class="pf-inline-form" id="delete-form">
                    <div v-if="err('delete_password')" class="pf-error mb-2">{{ err('delete_password') }}</div>
                    <form method="POST" :action="pf.urls.destroy" id="delete-account-form">
                        <input type="hidden" name="_token" :value="config.csrf">
                        <input type="hidden" name="_method" value="DELETE">
                        <div class="pf-field">
                            <label class="pf-label">Şifrenizi girin <span class="pf-req">*</span></label>
                            <input class="pf-input" type="password" name="delete_password" placeholder="••••••••" data-testid="account-delete-password">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="pf-btn-save" style="background:#ef4444;border-color:#ef4444;" onclick="openDeleteModal()" data-testid="account-delete-submit">
                                <i class="bi bi-trash me-1"></i> Hesabımı Kalıcı Olarak Sil
                            </button>
                            <button type="button" class="pf-btn-reset" onclick="toggleInline('delete-form')">Vazgeç</button>
                        </div>
                    </form>

                    <div class="pf-modal-overlay" id="deleteModal" data-testid="delete-modal">
                        <div class="pf-modal">
                            <div class="pf-modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                            <div class="pf-modal-title">Hesabını silmek üzeresin</div>
                            <div class="pf-modal-text">Bu işlem <strong>geri alınamaz</strong>. Tüm ilanların, tekliflerin ve verilerin kalıcı olarak silinecek.</div>
                            <div class="pf-modal-actions">
                                <button type="button" class="pf-btn-reset" onclick="closeDeleteModal()" data-testid="delete-modal-cancel">Vazgeç</button>
                                <button type="button" class="pf-modal-confirm" onclick="submitDeleteAccount()" data-testid="delete-modal-confirm">
                                    <i class="bi bi-trash me-1"></i> Evet, Sil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GİZLİLİK -->
            <div id="ep-gizlilik" class="pf-epanel">
                <form method="POST" :action="pf.urls.privacy">
                    <input type="hidden" name="_token" :value="config.csrf">
                    <input type="hidden" name="_method" value="PUT">
                    <div class="pf-toggle-list">
                        <label class="pf-trow">
                            <div class="pf-trow-info"><div class="pf-trow-title">Profil herkese açık</div><div class="pf-trow-desc">Arama sonuçlarında görünsün</div></div>
                            <input type="hidden" name="profile_public" value="0">
                            <input type="checkbox" name="profile_public" value="1" class="pf-tog-input" :checked="pf.privacy.profile_public">
                        </label>
                        <label class="pf-trow">
                            <div class="pf-trow-info"><div class="pf-trow-title">Teklif geçmişi gizli</div><div class="pf-trow-desc">Verilen teklifler gizlensin</div></div>
                            <input type="hidden" name="bids_hidden" value="0">
                            <input type="checkbox" name="bids_hidden" value="1" class="pf-tog-input" :checked="pf.privacy.bids_hidden">
                        </label>
                        <label class="pf-trow">
                            <div class="pf-trow-info"><div class="pf-trow-title">Çevrimiçi göster</div><div class="pf-trow-desc">Diğer kullanıcılar sizi görebilsin</div></div>
                            <input type="hidden" name="show_online" value="0">
                            <input type="checkbox" name="show_online" value="1" class="pf-tog-input" :checked="pf.privacy.show_online">
                        </label>
                        <label class="pf-trow">
                            <div class="pf-trow-info"><div class="pf-trow-title">E-posta bildirimleri</div><div class="pf-trow-desc">Teklif ve ilan güncellemeleri</div></div>
                            <input type="hidden" name="email_notifications" value="0">
                            <input type="checkbox" name="email_notifications" value="1" class="pf-tog-input" :checked="pf.privacy.email_notifications">
                        </label>
                        <label class="pf-trow" style="border-bottom:none;">
                            <div class="pf-trow-info"><div class="pf-trow-title">Sadece takipten mesaj</div><div class="pf-trow-desc">Yabancılardan mesaj gelmesin</div></div>
                            <input type="hidden" name="messages_followers_only" value="0">
                            <input type="checkbox" name="messages_followers_only" value="1" class="pf-tog-input" :checked="pf.privacy.messages_followers_only">
                        </label>
                    </div>
                    <div class="pf-footer">
                        <span></span>
                        <button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- SOSYAL -->
            <div id="ep-sosyal" class="pf-epanel">
                <form method="POST" :action="pf.urls.social">
                    <input type="hidden" name="_token" :value="config.csrf">
                    <input type="hidden" name="_method" value="PUT">
                    <div v-for="row in socialRows" :key="row.key" class="pf-social-row">
                        <div class="pf-social-icon"><i class="bi" :class="row.icon"></i></div>
                        <div class="pf-input-pre" style="flex:1;">
                            <span class="pf-pre-label">{{ row.prefix }}</span>
                            <input type="text" :name="`social[${row.key}]`" :value="pf.social[row.key]" placeholder="kullanici_adi">
                        </div>
                    </div>
                    <div class="pf-footer">
                        <span></span>
                        <button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- İÇERİK -->
        <div class="pf-content-area">
            <div class="pf-tab-bar">
                <button class="pf-ptab active" onclick="switchPTab('vitrin',this)"><i class="bi bi-grid-3x3-gap-fill me-1"></i> Vitrin</button>
                <button class="pf-ptab" onclick="switchPTab('degerlendirmeler',this)"><i class="bi bi-star me-1"></i> Değerlendirmeler</button>
                <button class="pf-ptab" onclick="switchPTab('aktivite',this)"><i class="bi bi-activity me-1"></i> Aktivite</button>
            </div>

            <!-- VİTRİN -->
            <div id="pc-vitrin">
                <div v-if="pf.showcase.length" class="pf-grid">
                    <Link v-for="(a, ai) in pf.showcase" :key="ai" :href="a.url" class="pf-auction-card">
                        <div class="pf-card-img-wrap">
                            <img :src="a.cover" :alt="a.title">
                            <div class="pf-card-price">{{ a.price_fmt }}</div>
                            <div class="pf-card-badge"><span class="pf-pulse-dot"></span> Aktif</div>
                        </div>
                        <div class="pf-card-body">
                            <div class="pf-card-title">{{ a.title }}</div>
                            <div class="pf-card-meta">
                                <span><i class="bi bi-people me-1"></i>{{ a.bid_count }} teklif</span>
                                <span><i class="bi bi-eye me-1"></i>{{ a.view_count }}</span>
                            </div>
                        </div>
                    </Link>
                </div>
                <div v-else class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="pf-empty-title">Henüz aktif ilan yok</div>
                    <div class="pf-empty-sub">
                        <template v-if="pf.is_owner">İlan oluştur ve vitrininde görünsün.</template>
                        <template v-else>Bu kullanıcı henüz ilan yayınlamamış.</template>
                    </div>
                    <template v-if="pf.is_owner">
                        <Link v-if="pf.is_creator_seller" :href="pf.urls.seller_create" class="pf-btn-save mt-3 d-inline-flex align-items-center gap-1"><i class="bi bi-plus-lg"></i> İlan Oluştur</Link>
                        <Link v-else :href="pf.urls.browse" class="pf-btn-save mt-3 d-inline-flex align-items-center gap-1"><i class="bi bi-search"></i> Müzayedeleri Keşfet</Link>
                    </template>
                </div>
            </div>

            <!-- DEĞERLENDİRMELER -->
            <div id="pc-degerlendirmeler" style="display:none;">
                <div v-if="pf.reviews" class="au-card" data-testid="reviews-section">
                    <div class="rv-summary">
                        <div class="rv-score">{{ pf.reviews.rating_fmt }}</div>
                        <div>
                            <span class="stars">
                                <i v-for="(st, si) in pf.reviews.stars" :key="si" class="bi" :class="st === 'full' ? 'bi-star-fill' : (st === 'half' ? 'bi-star-half' : 'bi-star')"></i>
                            </span>
                            <div class="rv-count">{{ pf.reviews.review_count }} değerlendirme</div>
                        </div>
                    </div>

                    <template v-if="$page.props.auth.user && !pf.is_owner">
                        <form v-if="pf.reviews.can_review" :action="pf.reviews.store_url" method="POST" class="rv-form" data-testid="review-form">
                            <input type="hidden" name="_token" :value="config.csrf">
                            <div class="rv-stars-input" id="rvStars">
                                <i v-for="i in 5" :key="i" class="bi" :class="(pf.reviews.my_review && pf.reviews.my_review.rating >= i) ? 'bi-star-fill' : 'bi-star'" :data-val="i" :data-testid="`review-star-${i}`"></i>
                            </div>
                            <input type="hidden" name="rating" id="rvRating" :value="pf.reviews.my_review ? pf.reviews.my_review.rating : 5">
                            <textarea name="comment" class="pf-input rv-textarea" rows="2" placeholder="Satıcı hakkında görüşün...">{{ pf.reviews.my_review ? pf.reviews.my_review.comment : '' }}</textarea>
                            <button class="rv-submit" type="submit" data-testid="review-submit">{{ pf.reviews.my_review ? 'Değerlendirmeyi Güncelle' : 'Değerlendir' }}</button>
                        </form>
                        <div v-else-if="pf.reviews.locked" class="rv-locked" data-testid="review-locked">
                            <i class="bi bi-lock"></i>
                            Yalnızca bu satıcıdan ürün alıp teslim aldığınız (tamamlanan sipariş) durumda değerlendirme yapabilirsiniz.
                        </div>
                    </template>

                    <div class="rv-list">
                        <template v-if="pf.reviews.items.length">
                            <div v-for="(rev, ri) in pf.reviews.items" :key="ri" class="rv-item" data-testid="review-item">
                                <img class="rv-ava" :src="rev.avatar" alt="">
                                <div class="rv-body">
                                    <div class="rv-head">
                                        <span class="rv-name">{{ rev.name }}</span>
                                        <span class="stars">
                                            <i v-for="(st, si) in rev.stars" :key="si" class="bi" :class="st === 'full' ? 'bi-star-fill' : (st === 'half' ? 'bi-star-half' : 'bi-star')"></i>
                                        </span>
                                    </div>
                                    <div v-if="rev.comment" class="rv-comment">{{ rev.comment }}</div>
                                    <div class="rv-time">{{ rev.time }}</div>
                                </div>
                            </div>
                        </template>
                        <div v-else class="rv-empty">Henüz değerlendirme yok. İlk değerlendirmeyi sen yap!</div>
                    </div>
                </div>
                <div v-else class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-star"></i></div>
                    <div class="pf-empty-title">Bu kullanıcı bir satıcı değil</div>
                    <div class="pf-empty-sub">Yalnızca satıcılar değerlendirilebilir.</div>
                </div>
            </div>

            <!-- AKTİVİTE -->
            <div id="pc-aktivite" style="display:none;">
                <div v-if="!pf.activities.length" class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-activity"></i></div>
                    <div class="pf-empty-title">Aktivite bulunamadı</div>
                    <div class="pf-empty-sub">Teklif verdiğinde veya bir açık artırma kazandığında burada görünecek.</div>
                </div>
                <div v-else class="pf-activity" data-testid="activity-list">
                    <component :is="act.url ? Link : 'div'" v-for="(act, aci) in pf.activities" :key="aci" class="pf-act-item" :href="act.url || undefined">
                        <span class="pf-act-icon" :style="`background:${act.color}22;color:${act.color};`"><i class="bi" :class="act.icon"></i></span>
                        <span class="pf-act-body">
                            <span class="pf-act-title">{{ act.title }}</span>
                            <span class="pf-act-subject">{{ act.subject }}</span>
                        </span>
                        <span class="pf-act-right">
                            <span class="pf-act-amount">{{ act.amount_fmt }}</span>
                            <span class="pf-act-date">{{ act.date }}</span>
                        </span>
                    </component>
                </div>
            </div>
        </div>
    </div>

    <!-- Config root — profile-show.js okur -->
    <div id="profileShowRoot"
         :data-public-url="config.public_url"
         :data-drawer-open="config.drawer_open"
         :data-drawer-tab="config.drawer_tab"
         :data-drawer-inline="config.drawer_inline"
         :data-error-fields="JSON.stringify(config.error_fields)"></div>
</template>
