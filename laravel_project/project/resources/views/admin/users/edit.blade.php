@extends('layouts.app')
@section('title', 'Düzenle — ' . $user->name)
@section('content')

@php
    $roleKey   = $user->roles->first()?->name ?? 'user';
    $roleLabel = match($roleKey) { 'admin' => '👑 Admin', 'seller' => '🏪 Onaylı Satıcı', default => '🛍️ Üye' };
@endphp

<div id="adminUserEditRoot"
     data-default-name="{{ $user->name }}"
     data-default-email="{{ $user->email }}"></div>

<div class="pf-root">

    <div class="pf-top">
        <div class="pf-cover"></div>

        <div class="pf-identity">
            <div class="pf-avatar-wrap">
                <div class="pf-avatar-outer">
                    <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=155eef&color=fff&size=256' }}"
                         alt="{{ $user->name }}"
                         id="heroAvatar"
                         class="pf-avatar-img">
                </div>
            </div>
            <div class="pf-identity-right">
                <div>
                    <div class="pf-uname-row">
                        <span class="pf-uname" id="heroName">{{ $user->name }}</span>
                        <span class="pf-role-badge">{{ $roleLabel }}</span>
                    </div>
                    <div class="pf-bio" id="heroEmail">{{ $user->email }}</div>
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
                <div class="pf-stat-num">#{{ $user->id }}</div>
                <div class="pf-stat-label">ID</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $user->created_at->format('Y') }}</div>
                <div class="pf-stat-label">KAYIT</div>
            </div>
        </div>

        <div class="pf-action-row" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:12px;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color:var(--primary)">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" style="color:var(--primary)">Kullanıcılar</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user) }}" style="color:var(--primary)">{{ $user->name }}</a></li>
                    <li class="breadcrumb-item active" style="color:var(--muted)">Düzenle</li>
                </ol>
            </nav>
            <a href="{{ route('admin.users.show', $user) }}" class="pf-btn-reset" style="height:36px;padding:0 14px;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-arrow-left"></i> Geri
            </a>
        </div>
    </div>

    <div class="pf-edit-drawer open">

        <div class="pf-edit-tabs">
            <button class="pf-etab active" onclick="switchETab('genel',this)">
                <i class="bi bi-person me-1"></i> Genel
            </button>
            <button class="pf-etab" onclick="switchETab('guvenlik',this)">
                <i class="bi bi-shield me-1"></i> Güvenlik
            </button>
            <button class="pf-etab" onclick="switchETab('rol',this)">
                <i class="bi bi-person-badge me-1"></i> Rol & Durum
            </button>
        </div>

        <div id="ep-genel" class="pf-epanel active">

            @if($errors->any())
                <div class="pf-alert-success" style="background:rgba(248,113,113,.1);border-color:rgba(248,113,113,.3);margin-bottom:16px;">
                    <i class="bi bi-exclamation-circle-fill" style="color:#f87171;"></i>
                    <span style="color:#f87171;">
                        @foreach($errors->all() as $err){{ $err }}@if(!$loop->last) · @endif @endforeach
                    </span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="pf-avatar-upload-row">
                    <label for="avatar" class="pf-upload-avatar" style="cursor:pointer;" title="Fotoğraf değiştir">
                        <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=155eef&color=fff&size=256' }}"
                             alt="{{ $user->name }}" id="avatarPreviewSmall"
                             style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <input type="file" id="avatar" name="avatar" accept=".png,.jpg,.jpeg,.webp" class="d-none">
                    </label>
                    <div>
                        <div class="pf-upload-title">Profil fotoğrafı</div>
                        <div class="pf-upload-desc">PNG, JPG, WEBP · Maks. 2MB</div>
                        <label for="avatar" class="pf-btn-photo mt-2 d-inline-flex align-items-center gap-1" style="cursor:pointer;">
                            <i class="bi bi-upload"></i> Fotoğraf yükle
                        </label>
                    </div>
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Ad Soyad <span class="pf-req">*</span></label>
                        <input class="pf-input" type="text" name="name" id="inputName"
                               value="{{ old('name', $user->name) }}" placeholder="Ad Soyad">
                        @error('name') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">E-posta <span class="pf-req">*</span></label>
                        <input class="pf-input" type="email" name="email" id="inputEmail"
                               value="{{ old('email', $user->email) }}" placeholder="eposta@domain.com">
                        @error('email') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Telefon</label>
                        <div class="pf-input-pre">
                            <span class="pf-pre-label">+90</span>
                            <input type="tel" name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   maxlength="15" placeholder="5xx xxx xx xx">
                        </div>
                        @error('phone') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Kullanıcı Adı</label>
                        <div class="pf-input-pre">
                            <span class="pf-pre-label">@</span>
                            <input type="text" name="username"
                                   value="{{ old('username', $user->username) }}"
                                   maxlength="30" placeholder="kullanici_adi">
                        </div>
                        @error('username') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-field">
                    <label class="pf-label">Hakkında</label>
                    <div style="position:relative;">
                        <textarea class="pf-input" name="bio" rows="3" maxlength="300">{{ old('bio', $user->bio) }}</textarea>
                    </div>
                    @error('bio') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-footer">
                    <span class="pf-save-info">
                        <i class="bi bi-person-gear"></i> Admin düzenlemesi
                    </span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.show', $user) }}" class="pf-btn-reset">İptal</a>
                        <button type="submit" class="pf-btn-save" id="saveBtn">
                            <i class="bi bi-floppy me-1"></i> Kaydet
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="ep-guvenlik" class="pf-epanel">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <input type="hidden" name="name"  value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="pf-sec-item">
                    <div class="pf-sec-icon" style="background:rgba(99,102,241,.12);">
                        <i class="bi bi-lock" style="color:#818cf8;"></i>
                    </div>
                    <div class="pf-sec-info">
                        <div class="pf-sec-title">Şifre Değiştir</div>
                        <div class="pf-sec-sub">Boş bırakırsan şifre değişmez</div>
                    </div>
                </div>

                <div class="pf-two-col" style="margin-top:16px;">
                    <div class="pf-field">
                        <label class="pf-label">Yeni Şifre</label>
                        <div style="position:relative;">
                            <input class="pf-input" type="password" name="password" id="pw1"
                                   placeholder="••••••••" style="padding-right:40px;">
                            <button type="button" onclick="togglePw('pw1',this)"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;">
                                <i class="bi bi-eye-slash" id="pw1-icon"></i>
                            </button>
                        </div>
                        <div class="pf-pass-bars">
                            <div class="pf-pbar" id="pb1"></div>
                            <div class="pf-pbar" id="pb2"></div>
                            <div class="pf-pbar" id="pb3"></div>
                            <div class="pf-pbar" id="pb4"></div>
                        </div>
                        @error('password') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Tekrar</label>
                        <div style="position:relative;">
                            <input class="pf-input" type="password" name="password_confirmation" id="pw2"
                                   placeholder="••••••••" style="padding-right:40px;">
                            <button type="button" onclick="togglePw('pw2',this)"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;">
                                <i class="bi bi-eye-slash" id="pw2-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="pf-hint mb-4">En az 8 karakter, büyük/küçük harf ve sembol içermeli.</div>

                <div class="pf-footer">
                    <span></span>
                    <button type="submit" class="pf-btn-save">
                        <i class="bi bi-floppy me-1"></i> Şifreyi Güncelle
                    </button>
                </div>
            </form>
        </div>

        <div id="ep-rol" class="pf-epanel">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <input type="hidden" name="name"  value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="pf-field">
                    <label class="pf-label">Rol <span class="pf-req">*</span></label>
                    <select name="role" class="pf-input">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-toggle-list mt-3">
                    <label class="pf-trow" style="border-bottom:none;">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Hesap Doğrulaması</div>
                            <div class="pf-trow-desc">Kullanıcıyı manuel olarak doğrula</div>
                        </div>
                        <input type="hidden" name="is_verified" value="0">
                        <input type="checkbox" name="is_verified" value="1" class="pf-tog-input"
                               {{ old('is_verified', $user->is_verified) ? 'checked' : '' }}>
                    </label>
                </div>

                <div class="pf-footer" style="margin-top:20px;">
                    <span class="pf-save-info">
                        <i class="bi bi-shield-lock"></i> Rol değişikliği anında aktif olur
                    </span>
                    <button type="submit" class="pf-btn-save">
                        <i class="bi bi-floppy me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/admin-users-edit.js') }}"></script>
@endpush
