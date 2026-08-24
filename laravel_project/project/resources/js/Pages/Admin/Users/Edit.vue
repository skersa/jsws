<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ user: Object, roles: Array });
const u = props.user;
const tab = ref('genel');

const form = useForm({
    name: u.name,
    email: u.email,
    phone: u.phone || '',
    username: u.username || '',
    bio: u.bio || '',
    avatar: null,
    password: '',
    password_confirmation: '',
    role: u.role,
    is_verified: !!u.is_verified,
});

const heroName = ref(u.name);
const heroEmail = ref(u.email);
const preview = ref(u.avatar_url);
const avatarEl = ref(null);
function onAvatar(e) {
    const f = e.target.files[0]; if (!f) return;
    form.avatar = f; preview.value = URL.createObjectURL(f);
}
const showPw1 = ref(false); const showPw2 = ref(false);
const pwStrength = computed(() => {
    const p = form.password || ''; let s = 0;
    if (p.length >= 8) s++; if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++; if (/[0-9]/.test(p)) s++; if (/[^A-Za-z0-9]/.test(p)) s++;
    return s;
});
const errList = computed(() => Object.values(form.errors));

function submit() {
    form.transform((d) => ({ ...d, _method: 'put' })).post(u.update_url, { forceFormData: true });
}
</script>

<template>
    <Head :title="'Düzenle — ' + u.name" />
    <div class="pf-root">
        <div class="pf-top">
            <div class="pf-cover"></div>
            <div class="pf-identity">
                <div class="pf-avatar-wrap"><div class="pf-avatar-outer"><img :src="preview" :alt="heroName" class="pf-avatar-img"></div></div>
                <div class="pf-identity-right">
                    <div>
                        <div class="pf-uname-row"><span class="pf-uname">{{ heroName }}</span><span class="pf-role-badge">{{ u.role_label }}</span></div>
                        <div class="pf-bio">{{ heroEmail }}</div>
                    </div>
                </div>
            </div>
            <div class="pf-stats-row">
                <div class="pf-stat"><div class="pf-stat-num">{{ u.auctions_count }}</div><div class="pf-stat-label">İLAN</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ u.bids_count }}</div><div class="pf-stat-label">TEKLİF</div></div>
                <div class="pf-stat"><div class="pf-stat-num">#{{ u.id }}</div><div class="pf-stat-label">ID</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ u.created_year }}</div><div class="pf-stat-label">KAYIT</div></div>
            </div>
            <div class="pf-action-row" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:12px;">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" style="color:var(--primary)">Admin</Link></li>
                        <li class="breadcrumb-item"><Link :href="u.index_url" style="color:var(--primary)">Kullanıcılar</Link></li>
                        <li class="breadcrumb-item"><Link :href="u.show_url" style="color:var(--primary)">{{ u.name }}</Link></li>
                        <li class="breadcrumb-item active" style="color:var(--muted)">Düzenle</li>
                    </ol>
                </nav>
                <Link :href="u.show_url" class="pf-btn-reset" style="height:36px;padding:0 14px;display:flex;align-items:center;gap:6px;"><i class="bi bi-arrow-left"></i> Geri</Link>
            </div>
        </div>

        <div class="pf-edit-drawer open">
            <div class="pf-edit-tabs">
                <button class="pf-etab" :class="{ active: tab==='genel' }" @click="tab='genel'" type="button"><i class="bi bi-person me-1"></i> Genel</button>
                <button class="pf-etab" :class="{ active: tab==='guvenlik' }" @click="tab='guvenlik'" type="button"><i class="bi bi-shield me-1"></i> Güvenlik</button>
                <button class="pf-etab" :class="{ active: tab==='rol' }" @click="tab='rol'" type="button"><i class="bi bi-person-badge me-1"></i> Rol &amp; Durum</button>
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data">
                <div v-if="errList.length" class="pf-alert-success" style="background:rgba(248,113,113,.1);border-color:rgba(248,113,113,.3);margin:0 22px 16px;">
                    <i class="bi bi-exclamation-circle-fill" style="color:#f87171;"></i>
                    <span style="color:#f87171;">{{ errList.join(' · ') }}</span>
                </div>

                <!-- GENEL -->
                <div class="pf-epanel" :class="{ active: tab==='genel' }">
                    <div class="pf-avatar-upload-row">
                        <label class="pf-upload-avatar" style="cursor:pointer;" title="Fotoğraf değiştir">
                            <img :src="preview" :alt="u.name" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            <input ref="avatarEl" type="file" accept=".png,.jpg,.jpeg,.webp" class="d-none" @change="onAvatar" data-testid="user-avatar">
                        </label>
                        <div>
                            <div class="pf-upload-title">Profil fotoğrafı</div>
                            <div class="pf-upload-desc">PNG, JPG, WEBP · Maks. 2MB</div>
                            <label class="pf-btn-photo mt-2 d-inline-flex align-items-center gap-1" style="cursor:pointer;" @click="avatarEl?.click()"><i class="bi bi-upload"></i> Fotoğraf yükle</label>
                        </div>
                    </div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Ad Soyad <span class="pf-req">*</span></label>
                            <input class="pf-input" type="text" v-model="form.name" @input="heroName=form.name" placeholder="Ad Soyad" data-testid="user-name">
                            <div v-if="form.errors.name" class="pf-error">{{ form.errors.name }}</div>
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">E-posta <span class="pf-req">*</span></label>
                            <input class="pf-input" type="email" v-model="form.email" @input="heroEmail=form.email" placeholder="eposta@domain.com" data-testid="user-email">
                            <div v-if="form.errors.email" class="pf-error">{{ form.errors.email }}</div>
                        </div>
                    </div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Telefon</label>
                            <div class="pf-input-pre"><span class="pf-pre-label">+90</span><input type="tel" v-model="form.phone" maxlength="15" placeholder="5xx xxx xx xx"></div>
                            <div v-if="form.errors.phone" class="pf-error">{{ form.errors.phone }}</div>
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Kullanıcı Adı</label>
                            <div class="pf-input-pre"><span class="pf-pre-label">@</span><input type="text" v-model="form.username" maxlength="30" placeholder="kullanici_adi"></div>
                        </div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Hakkında</label>
                        <textarea class="pf-input" v-model="form.bio" rows="3" maxlength="300"></textarea>
                    </div>
                    <div class="pf-footer">
                        <span class="pf-save-info"><i class="bi bi-person-gear"></i> Admin düzenlemesi</span>
                        <div class="d-flex gap-2">
                            <Link :href="u.show_url" class="pf-btn-reset">İptal</Link>
                            <button type="submit" class="pf-btn-save" :disabled="form.processing" data-testid="user-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                        </div>
                    </div>
                </div>

                <!-- GÜVENLİK -->
                <div class="pf-epanel" :class="{ active: tab==='guvenlik' }">
                    <div class="pf-sec-item">
                        <div class="pf-sec-icon" style="background:rgba(99,102,241,.12);"><i class="bi bi-lock" style="color:#818cf8;"></i></div>
                        <div class="pf-sec-info"><div class="pf-sec-title">Şifre Değiştir</div><div class="pf-sec-sub">Boş bırakırsan şifre değişmez</div></div>
                    </div>
                    <div class="pf-two-col" style="margin-top:16px;">
                        <div class="pf-field">
                            <label class="pf-label">Yeni Şifre</label>
                            <div style="position:relative;">
                                <input class="pf-input" :type="showPw1 ? 'text' : 'password'" v-model="form.password" placeholder="••••••••" style="padding-right:40px;" data-testid="user-password">
                                <button type="button" @click="showPw1=!showPw1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;"><i class="bi" :class="showPw1 ? 'bi-eye' : 'bi-eye-slash'"></i></button>
                            </div>
                            <div class="pf-pass-bars">
                                <div v-for="n in 4" :key="n" class="pf-pbar" :style="{ background: pwStrength >= n ? (pwStrength>=3?'#10b981':'#fbbf24') : '' }"></div>
                            </div>
                            <div v-if="form.errors.password" class="pf-error">{{ form.errors.password }}</div>
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Tekrar</label>
                            <div style="position:relative;">
                                <input class="pf-input" :type="showPw2 ? 'text' : 'password'" v-model="form.password_confirmation" placeholder="••••••••" style="padding-right:40px;">
                                <button type="button" @click="showPw2=!showPw2" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;"><i class="bi" :class="showPw2 ? 'bi-eye' : 'bi-eye-slash'"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="pf-hint mb-4">En az 8 karakter, büyük/küçük harf ve sembol içermeli.</div>
                    <div class="pf-footer">
                        <span></span>
                        <button type="submit" class="pf-btn-save" :disabled="form.processing"><i class="bi bi-floppy me-1"></i> Şifreyi Güncelle</button>
                    </div>
                </div>

                <!-- ROL & DURUM -->
                <div class="pf-epanel" :class="{ active: tab==='rol' }">
                    <div class="pf-field">
                        <label class="pf-label">Rol <span class="pf-req">*</span></label>
                        <select v-model="form.role" class="pf-input" data-testid="user-role">
                            <option v-for="r in roles" :key="r.name" :value="r.name">{{ r.label }}</option>
                        </select>
                        <div v-if="form.errors.role" class="pf-error">{{ form.errors.role }}</div>
                    </div>
                    <div class="pf-toggle-list mt-3">
                        <label class="pf-trow" style="border-bottom:none;">
                            <div class="pf-trow-info"><div class="pf-trow-title">Hesap Doğrulaması</div><div class="pf-trow-desc">Kullanıcıyı manuel olarak doğrula</div></div>
                            <input type="checkbox" v-model="form.is_verified" class="pf-tog-input" data-testid="user-verified">
                        </label>
                    </div>
                    <div class="pf-footer" style="margin-top:20px;">
                        <span class="pf-save-info"><i class="bi bi-shield-lock"></i> Rol değişikliği anında aktif olur</span>
                        <button type="submit" class="pf-btn-save" :disabled="form.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
