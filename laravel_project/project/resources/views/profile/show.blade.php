@extends('layouts.app')
@section('title', $user->name . ' — Profil')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile-show.css') }}">
@endpush

@section('content')

@php
    $roleKey   = $user->roles->first()?->name ?? 'user';
    $roleLabel = match($roleKey) { 'admin' => '👑 Admin', 'seller' => '🏪 Onaylı Satıcı', default => '🛍️ Üye' };
    $isOwner   = auth()->id() === $user->id;

    \App\Models\Story::pruneExpired();
    $heroStories  = $user->stories()->where('expires_at', '>', now())->orderBy('id')->get();
    $hasStories   = $heroStories->isNotEmpty();
    $heroStoryIds = $heroStories->pluck('id')->values();
@endphp

<div class="pf-root">

    <div class="pf-top">

        <div class="pf-cover"></div>

        <div class="pf-identity">

            <div class="pf-avatar-wrap">
                @if($hasStories)
                <div class="story-item pf-avatar-story"
                     data-testid="profile-avatar-story"
                     data-story-uid="{{ $user->id }}"
                     data-story-ids='@json($heroStoryIds)'
                     data-ring-unseen="{{ story_ring_style($heroStories->count()) }}"
                     data-ring-seen="{{ story_ring_style($heroStories->count(), true) }}"
                     onclick="openStoryViewer({{ $user->id }})"
                     title="Hikayeleri gör" style="cursor:pointer;">
                    <div class="story-ring pf-avatar-ring" style="{{ story_ring_style($heroStories->count()) }}">
                        <img src="{{ $user->profile_img ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=155eef&color=fff&size=256' }}"
                             alt="{{ $user->name }}"
                             id="heroAvatar">
                    </div>
                </div>
                @else
                <div class="pf-avatar-outer">
                    <img src="{{ $user->profile_img ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=155eef&color=fff&size=256' }}"
                         alt="{{ $user->name }}"
                         id="heroAvatar"
                         class="pf-avatar-img">
                </div>
                @endif
                @if(method_exists($user,'isOnline') && $user->isOnline())
                    <span class="pf-online-dot"></span>
                @endif
            </div>

            <div class="pf-identity-right">
                <div>
                    <div class="pf-uname-row">
                        <span class="pf-uname" id="heroName">{{ $user->name }}</span>
                        <span class="pf-role-badge">{{ $roleLabel }}</span>
                    </div>
                    @if($user->username)
                        <div class="pf-handle" id="heroHandle">{{"@" . $user->username }}</div>
                    @endif
                    <div class="pf-bio" id="heroBio">
                        {{ $user->bio ?? 'Koleksiyon parçaları ve güvenli açık artırmanın adresi.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="pf-stats-row">
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $user->auctions()->count() }}</div>
                <div class="pf-stat-label">İLAN</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $user->bids()->count() }}</div>
                <div class="pf-stat-label">TEKLİF</div>
            </div>
            <div class="pf-stat">
                <a href="{{ route('profile.followers', $user->username) }}" class="pf-stat-link">
                    <div class="pf-stat-num" id="follower-count">{{ $followerCount }}</div>
                    <div class="pf-stat-label">TAKİPÇİ</div>
                </a>
            </div>
            <div class="pf-stat">
                <a href="{{ route('profile.following', $user->username) }}" class="pf-stat-link">
                    <div class="pf-stat-num">{{ $followingCount }}</div>
                    <div class="pf-stat-label">TAKİP</div>
                </a>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $user->isSeller() ? number_format($user->sellerRating(), 1) : '—' }}</div>
                <div class="pf-stat-label">PUAN</div>
            </div>
        </div>

        <div class="pf-action-row">
            @if($isOwner)
                <button class="pf-btn-edit" id="editToggle" onclick="toggleEdit()">
                    <i class="bi bi-pencil me-1"></i> Profili Düzenle
                </button>
                <button class="pf-btn-icon" aria-label="Paylaş">
                    <i class="bi bi-share"></i>
                </button>
            @else
                @auth
                    <button
                        id="follow-btn"
                        data-url="{{ route('follow.toggle', $user) }}"
                        class="pf-btn-primary {{ $isFollowing ? 'pf-btn-following' : '' }}"
                    >
                        @if($isFollowing)
                            <i class="bi bi-person-check-fill me-1"></i>
                            <span>Takibi Bırak</span>
                        @else
                            <i class="bi bi-person-plus me-1"></i>
                            <span>Takip Et</span>
                        @endif
                    </button>
                    <form action="{{ route('messages.start') }}" method="POST" class="pf-msg-form">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <button type="submit" class="pf-btn-secondary" data-testid="profile-message-btn">
                            <i class="bi bi-chat-dots me-1"></i> Mesaj
                        </button>
                    </form>
                @endauth
            @endif
        </div>
    </div>

    @if($hasStories)
    {{-- Hikayeler artık ayrı kart yerine profil resminden izlenir (Instagram tarzı) --}}
    @include('partials.story-viewer')
    @php
        $psHeroPayload = [
            'name'    => $user->name,
            'avatar'  => $user->profile_img,
            'isOwner' => (bool) $isOwner,
            'items'   => $heroStories->map(fn($st) => [
                'id'      => $st->id,
                'type'    => $st->media_type,
                'url'     => $st->url(),
                'caption' => $st->caption,
            ])->values(),
        ];
    @endphp
    <div class="story-source"
         data-user-id="{{ $user->id }}"
         data-story-payload='{{ json_encode($psHeroPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_APOS|JSON_HEX_QUOT) }}'></div>
    @endif

    @if($isOwner)
    <div class="pf-edit-drawer" id="editDrawer">

        <div class="pf-edit-tabs">
            <button class="pf-etab active" onclick="switchETab('genel',this)">
                <i class="bi bi-person me-1"></i> Genel
            </button>
            <button class="pf-etab" onclick="switchETab('guvenlik',this)">
                <i class="bi bi-shield me-1"></i> Güvenlik
            </button>
            <button class="pf-etab" onclick="switchETab('gizlilik',this)">
                <i class="bi bi-eye-slash me-1"></i> Gizlilik
            </button>
            <button class="pf-etab" onclick="switchETab('sosyal',this)">
                <i class="bi bi-link-45deg me-1"></i> Sosyal
            </button>
        </div>

        <div id="ep-genel" class="pf-epanel active">

            @if(session('profile_success'))
                <div class="pf-alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('profile_success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="pf-avatar-upload-row">
                    <label for="profile_image" class="pf-upload-avatar" style="cursor:pointer;" title="Fotoğraf değiştir">
                        <img src="{{ $user->profile_img ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=155eef&color=fff&size=256' }}"
                             alt="{{ $user->name }}" id="avatarPreviewSmall" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
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
                    <input class="pf-input @error('name') is-invalid @enderror" type="text" name="name"
                           value="{{ old('name',$user->name) }}"
                           placeholder="Ad">
                    @error('name') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label">Kullanıcı adı <span class="pf-req">*</span></label>
                    <div class="pf-input-pre">
                        <span class="pf-pre-label">@</span>
                        <input type="text" name="username" id="edit_username" @error('username') class="is-invalid" @enderror
                               value="{{ old('username', $user->username) }}"
                               maxlength="30" placeholder="kullanici_adi">
                    </div>
                    <div class="pf-hint">Sadece harf, rakam, nokta ve alt çizgi · 3–30 karakter</div>
                    @error('username') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label">GSM numarası</label>
                    <div class="pf-input-pre">
                        <span class="pf-pre-label">+90</span>
                        <input type="tel" name="phone"
                               value="{{ old('phone', $user->phone) }}" @error('phone') class="is-invalid" @enderror
                               maxlength="15" placeholder="5xx xxx xx xx" required>
                    </div>
                    @error('phone') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label">Hakkımda</label>
                    <div style="position:relative;">
                        <textarea class="pf-input" name="bio" id="bio_input" rows="3" @error('bio') class="is-invalid" @enderror
                                  maxlength="300"
                                  oninput="bioCount(this)">{{ old('bio', $user->bio) }}</textarea>
                        <span id="bio_counter" class="pf-char-cnt">{{ strlen(old('bio', $user->bio ?? '')) }}/300</span>
                    </div>
                    @error('bio') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label">E-posta</label>
                    <input class="pf-input" type="email" value="{{ $user->email }}" disabled style="opacity:.4;cursor:not-allowed;">
                    <div class="pf-hint">E-postayı değiştirmek için Güvenlik sekmesini kullan.</div>
                </div>

                <div class="pf-footer">
                    <span class="pf-save-info" id="saveInfo">
                        <i class="bi bi-clock"></i> Kaydedilmedi
                    </span>
                    <div class="d-flex gap-2">
                        <button type="reset" class="pf-btn-reset">Sıfırla</button>
                        <button type="submit" class="pf-btn-save">
                            <i class="bi bi-floppy me-1"></i> Kaydet
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="ep-guvenlik" class="pf-epanel">

            <div class="pf-sec-item">
                <div class="pf-sec-icon" style="background:rgba(99,102,241,.12);">
                    <i class="bi bi-envelope" style="color:#818cf8;"></i>
                </div>
                <div class="pf-sec-info">
                    <div class="pf-sec-title">E-posta adresi</div>
                    <div class="pf-sec-sub">{{ $user->email }}</div>
                </div>
                <button class="pf-btn-change" onclick="toggleInline('email-form')">Değiştir</button>
            </div>
            <div class="pf-inline-form" id="email-form">
                @if(session('email_success'))
                    <div class="pf-alert-success mb-3">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('email_success') }}</span>
                    </div>
                @endif
                <form method="POST" action="{{ route('profile.email') }}">
                    @csrf @method('PUT')
                    <div class="pf-two-col mb-3">
                        <div class="pf-field" style="margin-bottom:0;">
                            <label class="pf-label">Yeni e-posta <span class="pf-req">*</span></label>
                            <input class="pf-input" type="email" name="email" @error('email') class="is-invalid" @enderror
                                   placeholder="yeni@eposta.com"
                                   value="{{ old('email') }}">
                            @error('email') <div class="pf-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="pf-field" style="margin-bottom:0;">
                            <label class="pf-label">Mevcut şifre <span class="pf-req">*</span></label>
                            <input class="pf-input" type="password" name="confirmemailpassword" placeholder="••••••••">
                            @error('confirmemailpassword') <div class="pf-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="pf-btn-save">
                            <i class="bi bi-check me-1"></i> Güncelle
                        </button>
                        <button type="button" class="pf-btn-reset" onclick="toggleInline('email-form')">Vazgeç</button>
                    </div>
                </form>
            </div>

            <div class="pf-sec-item">
                <div class="pf-sec-icon" style="background:rgba(245,158,11,.1);">
                    <i class="bi bi-lock" style="color:#fbbf24;"></i>
                </div>
                <div class="pf-sec-info">
                    <div class="pf-sec-title">Şifre</div>
                    <div class="pf-sec-sub">Son değişim: bilinmiyor</div>
                </div>
                <button class="pf-btn-change" onclick="toggleInline('pass-form')">Değiştir</button>
            </div>
            <div class="pf-inline-form" id="pass-form">
                @if(session('password_success'))
                    <div class="pf-alert-success mb-3">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('password_success') }}</span>
                    </div>
                @endif
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="pf-field">
                        <label class="pf-label">Mevcut şifre <span class="pf-req">*</span></label>
                        <input class="pf-input" type="password" name="currentpassword" @error('password') class="is-invalid" @enderror placeholder="••••••••">
                        @error('currentpassword') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-two-col">
                        <div class="pf-field" style="margin-bottom:0;">
                            <label class="pf-label">Yeni şifre <span class="pf-req">*</span></label>
                            <input class="pf-input" type="password" name="password"
                                   id="new_pass" @error('password') class="is-invalid" @enderror placeholder="••••••••"
                                   oninput="passStrength(this)">
                            <div class="pf-pass-bars">
                                <div class="pf-pbar" id="pb1"></div>
                                <div class="pf-pbar" id="pb2"></div>
                                <div class="pf-pbar" id="pb3"></div>
                                <div class="pf-pbar" id="pb4"></div>
                            </div>
                            @error('password') <div class="pf-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="pf-field" style="margin-bottom:0;">
                            <label class="pf-label">Tekrar <span class="pf-req">*</span></label>
                            <input class="pf-input" type="password" name="password_confirmation" placeholder="••••••••">
                        </div>
                    </div>
                    <div class="pf-hint mt-2 mb-3">En az 8 karakter, büyük/küçük harf ve sembol içermeli.</div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="pf-btn-save">
                            <i class="bi bi-check me-1"></i> Güncelle
                        </button>
                        <button type="button" class="pf-btn-reset" onclick="toggleInline('pass-form')">Vazgeç</button>
                    </div>
                </form>
            </div>

            <div class="pf-sec-item" style="border-bottom:none;">
                <div class="pf-sec-icon" style="background:rgba(239,68,68,.1);">
                    <i class="bi bi-exclamation-triangle" style="color:#f87171;"></i>
                </div>
                <div class="pf-sec-info">
                    <div class="pf-sec-title" style="color:#f87171;">Hesabı sil</div>
                    <div class="pf-sec-sub">Tüm veriler kalıcı olarak silinir</div>
                </div>
                <button type="button" class="pf-btn-change" style="border-color:rgba(239,68,68,.3);color:#f87171;" onclick="toggleInline('delete-form')" data-testid="account-delete-toggle">Sil</button>
            </div>
            <div class="pf-inline-form" id="delete-form">
                @error('delete_password')<div class="pf-error mb-2">{{ $message }}</div>@enderror
                <form method="POST" action="{{ route('profile.destroy') }}" id="delete-account-form">
                    @csrf @method('DELETE')
                    @if($user->password)
                    <div class="pf-field">
                        <label class="pf-label">Şifrenizi girin <span class="pf-req">*</span></label>
                        <input class="pf-input" type="password" name="delete_password" placeholder="••••••••" data-testid="account-delete-password">
                    </div>
                    @endif
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

        <div id="ep-gizlilik" class="pf-epanel">
            <form method="POST" action="{{ route('profile.privacy') }}">
                @csrf @method('PUT')
                <div class="pf-toggle-list">
                    <label class="pf-trow">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Profil herkese açık</div>
                            <div class="pf-trow-desc">Arama sonuçlarında görünsün</div>
                        </div>
                        <input type="hidden" name="profile_public" value="0">
                        <input type="checkbox" name="profile_public" value="1" class="pf-tog-input"
                               {{ $user->profile_public ? 'checked' : '' }}>
                    </label>
                    <label class="pf-trow">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Teklif geçmişi gizli</div>
                            <div class="pf-trow-desc">Verilen teklifler gizlensin</div>
                        </div>
                        <input type="hidden" name="bids_hidden" value="0">
                        <input type="checkbox" name="bids_hidden" value="1" class="pf-tog-input"
                               {{ $user->bids_hidden ? 'checked' : '' }}>
                    </label>
                    <label class="pf-trow">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Çevrimiçi göster</div>
                            <div class="pf-trow-desc">Diğer kullanıcılar sizi görebilsin</div>
                        </div>
                        <input type="hidden" name="show_online" value="0">
                        <input type="checkbox" name="show_online" value="1" class="pf-tog-input"
                               {{ $user->show_online ? 'checked' : '' }}>
                    </label>
                    <label class="pf-trow">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">E-posta bildirimleri</div>
                            <div class="pf-trow-desc">Teklif ve ilan güncellemeleri</div>
                        </div>
                        <input type="hidden" name="email_notifications" value="0">
                        <input type="checkbox" name="email_notifications" value="1" class="pf-tog-input"
                               {{ $user->email_notifications ? 'checked' : '' }}>
                    </label>
                    <label class="pf-trow" style="border-bottom:none;">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Sadece takipten mesaj</div>
                            <div class="pf-trow-desc">Yabancılardan mesaj gelmesin</div>
                        </div>
                        <input type="hidden" name="messages_followers_only" value="0">
                        <input type="checkbox" name="messages_followers_only" value="1" class="pf-tog-input"
                               {{ $user->messages_followers_only ? 'checked' : '' }}>
                    </label>
                </div>
                <div class="pf-footer">
                    <span></span>
                    <button type="submit" class="pf-btn-save">
                        <i class="bi bi-floppy me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>

        <div id="ep-sosyal" class="pf-epanel">
            <form method="POST" action="{{ route('profile.social') }}">
                @csrf @method('PUT')
                @foreach([
                    ['instagram','bi-instagram','instagram.com/'],
                    ['twitter','bi-twitter-x','x.com/'],
                    ['youtube','bi-youtube','youtube.com/@'],
                    ['linkedin','bi-linkedin','linkedin.com/in/'],
                ] as [$key,$icon,$prefix])
                <div class="pf-social-row">
                    <div class="pf-social-icon">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div class="pf-input-pre" style="flex:1;">
                        <span class="pf-pre-label">{{ $prefix }}</span>
                        <input type="text" name="social[{{ $key }}]"
                               value="{{ old('social.'.$key, $user->social[$key] ?? '') }}"
                               placeholder="kullanici_adi">
                    </div>
                </div>
                @endforeach
                <div class="pf-footer">
                    <span></span>
                    <button type="submit" class="pf-btn-save">
                        <i class="bi bi-floppy me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>

    </div>
    @endif

    <div class="pf-content-area">
        <div class="pf-tab-bar">
            <button class="pf-ptab active" onclick="switchPTab('vitrin',this)">
                <i class="bi bi-grid-3x3-gap-fill me-1"></i> Vitrin
            </button>
            <button class="pf-ptab" onclick="switchPTab('degerlendirmeler',this)">
                <i class="bi bi-star me-1"></i> Değerlendirmeler
            </button>
            <button class="pf-ptab" onclick="switchPTab('aktivite',this)">
                <i class="bi bi-activity me-1"></i> Aktivite
            </button>
        </div>

        <div id="pc-vitrin">
            @if($user->auctions()->count() > 0)
                <div class="pf-grid">
                    @foreach($user->auctions as $auction)
                        <a href="{{ route('auctions.show', $auction) }}" class="pf-auction-card">
                            <div class="pf-card-img-wrap">
                                <img src="{{ $auction->coverUrl() }}"
                                     alt="{{ $auction->title }}">
                                <div class="pf-card-price">
                                    {{ number_format($auction->current_price ?? $auction->starting_price, 0, ',', '.') }} ₺
                                </div>
                                <div class="pf-card-badge">
                                    <span class="pf-pulse-dot"></span> Aktif
                                </div>
                            </div>
                            <div class="pf-card-body">
                                <div class="pf-card-title">{{ $auction->title }}</div>
                                <div class="pf-card-meta">
                                    <span><i class="bi bi-people me-1"></i>{{ $auction->bids()->count() }} teklif</span>
                                    <span><i class="bi bi-eye me-1"></i>{{ $auction->view_count ?? 0 }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="pf-empty-title">Henüz aktif ilan yok</div>
                    <div class="pf-empty-sub">
                        @if($isOwner) İlan oluştur ve vitrininde görünsün.
                        @else Bu kullanıcı henüz ilan yayınlamamış.
                        @endif
                    </div>
                    @if($isOwner)
                        @if($user->isSeller())
                            <a href="{{ route('seller.auctions.create') }}" class="pf-btn-save mt-3 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-plus-lg"></i> İlan Oluştur
                            </a>
                        @else
                            <a href="{{ route('browse.auctions') }}" class="pf-btn-save mt-3 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-search"></i> Müzayedeleri Keşfet
                            </a>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        <div id="pc-degerlendirmeler" style="display:none;">
            @if($user->isSeller())
                <div class="au-card" data-testid="reviews-section">
                    <div class="rv-summary">
                        <div class="rv-score">{{ number_format($user->sellerRating(), 1) }}</div>
                        <div>
                            @include('partials.stars', ['rating' => $user->sellerRating()])
                            <div class="rv-count">{{ $user->sellerReviewCount() }} değerlendirme</div>
                        </div>
                    </div>

                    @auth
                        @if(! $isOwner)
                            @if(auth()->user()->hasCompletedOrderFrom($user->id))
                                @php $myReview = $user->reviewFrom(auth()->user()); @endphp
                                @if(session('status'))<div class="pf-alert-success">{{ session('status') }}</div>@endif
                                @if(session('error'))<div class="pf-error rv-flash-error">{{ session('error') }}</div>@endif
                                <form action="{{ route('reviews.store', $user->username) }}" method="POST" class="rv-form" data-testid="review-form">
                                    @csrf
                                    <div class="rv-stars-input" id="rvStars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $myReview && $myReview->rating >= $i ? 'bi-star-fill' : 'bi-star' }}" data-val="{{ $i }}" data-testid="review-star-{{ $i }}"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" id="rvRating" value="{{ $myReview->rating ?? 5 }}">
                                    <textarea name="comment" class="pf-input rv-textarea" rows="2" placeholder="Satıcı hakkında görüşün...">{{ $myReview->comment ?? '' }}</textarea>
                                    <button class="rv-submit" type="submit" data-testid="review-submit">{{ $myReview ? 'Değerlendirmeyi Güncelle' : 'Değerlendir' }}</button>
                                </form>
                            @else
                                <div class="rv-locked" data-testid="review-locked">
                                    <i class="bi bi-lock"></i>
                                    Yalnızca bu satıcıdan ürün alıp teslim aldığınız (tamamlanan sipariş) durumda değerlendirme yapabilirsiniz.
                                </div>
                            @endif
                        @endif
                    @endauth

                    <div class="rv-list">
                        @forelse($user->reviewsReceived()->with('reviewer')->latest()->take(20)->get() as $rev)
                            <div class="rv-item" data-testid="review-item">
                                <img class="rv-ava" src="{{ $rev->reviewer->profile_img }}" alt="">
                                <div class="rv-body">
                                    <div class="rv-head">
                                        <span class="rv-name">{{ $rev->reviewer->name }}</span>
                                        @include('partials.stars', ['rating' => $rev->rating])
                                    </div>
                                    @if($rev->comment)<div class="rv-comment">{{ $rev->comment }}</div>@endif
                                    <div class="rv-time">{{ $rev->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="rv-empty">Henüz değerlendirme yok. İlk değerlendirmeyi sen yap!</div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-star"></i></div>
                    <div class="pf-empty-title">Bu kullanıcı bir satıcı değil</div>
                    <div class="pf-empty-sub">Yalnızca satıcılar değerlendirilebilir.</div>
                </div>
            @endif
        </div>

        <div id="pc-aktivite" style="display:none;">
            @if($activities->isEmpty())
                <div class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-activity"></i></div>
                    <div class="pf-empty-title">Aktivite bulunamadı</div>
                    <div class="pf-empty-sub">Teklif verdiğinde veya bir açık artırma kazandığında burada görünecek.</div>
                </div>
            @else
                <div class="pf-activity" data-testid="activity-list">
                    @foreach($activities as $act)
                        <a class="pf-act-item" @if($act['url']) href="{{ $act['url'] }}" @endif>
                            <span class="pf-act-icon" style="background:{{ $act['color'] }}22;color:{{ $act['color'] }};">
                                <i class="bi {{ $act['icon'] }}"></i>
                            </span>
                            <span class="pf-act-body">
                                <span class="pf-act-title">{{ $act['title'] }}</span>
                                <span class="pf-act-subject">{{ \Illuminate\Support\Str::limit($act['subject'], 52) }}</span>
                            </span>
                            <span class="pf-act-right">
                                <span class="pf-act-amount">{{ number_format((float) $act['amount'], 0, ',', '.') }} ₺</span>
                                <span class="pf-act-date">{{ $act['date']?->diffForHumans() }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

@php
    $psDrawerOpen = session('profile_success') || session('email_success') || session('password_success');
    $psDrawerTab  = (session('email_success') || session('password_success')) ? 'guvenlik' : '';
@endphp

<div id="profileShowRoot"
     data-public-url="{{ route('profile.public',$user->username) }}"
     data-drawer-open="{{ $psDrawerOpen ? '1' : '0' }}"
     data-drawer-tab="{{ $psDrawerTab }}"
     data-error-fields='@json($errors->keys())'></div>

@push('scripts')
<script src="{{ asset('assets/js/custom/profile-show.js') }}"></script>
@endpush

@endsection
