<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function edit()
    {
        return $this->renderProfile(auth()->user());
    }

    private function renderProfile(User $user)
    {
        $isOwner = auth()->check() && auth()->id() === $user->id;
        $isFollowing = auth()->check() ? auth()->user()->isFollowing($user->id) : false;
        $followerCount = $user->followers()->count();
        $followingCount = $user->followings()->count();
        $activities = $this->buildActivities($user, $isOwner);

        $starsOf = function ($rating) {
            $r = round(((float) $rating) * 2) / 2;
            $out = [];
            for ($i = 1; $i <= 5; $i++) {
                $out[] = $r >= $i ? 'full' : ($r >= $i - 0.5 ? 'half' : 'empty');
            }
            return $out;
        };
        $fmt = fn ($n) => number_format((float) $n, 0, ',', '.') . ' ₺';

        $roleKey = $user->roles->first()?->name ?? 'user';
        $roleLabel = match ($roleKey) { 'admin' => '👑 Admin', 'seller' => '🏪 Onaylı Satıcı', default => '🛍️ Üye' };

        \App\Models\Story::pruneExpired();
        $heroStories = $user->stories()->where('expires_at', '>', now())->orderBy('id')->get();

        $showcase = $user->auctions->map(fn ($a) => [
            'url' => route('auctions.show', $a),
            'cover' => $a->coverUrl(),
            'price_fmt' => number_format($a->current_price ?? $a->starting_price, 0, ',', '.') . ' ₺',
            'title' => $a->title,
            'bid_count' => $a->bids()->count(),
            'view_count' => $a->view_count ?? 0,
        ])->values();

        $reviews = null;
        if ($user->isSeller()) {
            $myReview = auth()->check() ? $user->reviewFrom(auth()->user()) : null;
            $reviews = [
                'rating_fmt' => number_format($user->sellerRating(), 1),
                'stars' => $starsOf($user->sellerRating()),
                'review_count' => $user->sellerReviewCount(),
                'can_review' => auth()->check() && ! $isOwner && auth()->user()->hasCompletedOrderFrom($user->id),
                'locked' => auth()->check() && ! $isOwner && ! auth()->user()->hasCompletedOrderFrom($user->id),
                'store_url' => route('reviews.store', $user->username),
                'my_review' => $myReview ? ['rating' => $myReview->rating, 'comment' => $myReview->comment] : null,
                'items' => $user->reviewsReceived()->with('reviewer')->latest()->take(20)->get()->map(fn ($rev) => [
                    'avatar' => $rev->reviewer->profile_img,
                    'name' => $rev->reviewer->name,
                    'stars' => $starsOf($rev->rating),
                    'comment' => $rev->comment,
                    'time' => $rev->created_at->diffForHumans(),
                ])->values(),
            ];
        }

        $errorBag = session('errors');
        $errorFields = $errorBag ? array_values($errorBag->getBag('default')->keys()) : [];
        $errorMessages = $errorBag ? $errorBag->getBag('default')->getMessages() : [];
        $errorFlat = [];
        foreach ($errorMessages as $k => $msgs) {
            $errorFlat[$k] = $msgs[0] ?? '';
        }

        $psDrawerOpen = session('profile_success') || session('email_success') || session('password_success');
        $psDrawerTab = (session('email_success') || session('password_success')) ? 'guvenlik' : '';
        $psDrawerInline = session('email_success') ? 'email-form' : (session('password_success') ? 'pass-form' : '');

        $avatarFallback = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=155eef&color=fff&size=256';

        $pf = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'handle' => $user->username ? '@' . $user->username : null,
                'bio' => $user->bio,
                'bio_display' => $user->bio ?? 'Koleksiyon parçaları ve güvenli açık artırmanın adresi.',
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->profile_img ?? $avatarFallback,
                'role_label' => $roleLabel,
                'is_online' => method_exists($user, 'isOnline') && $user->isOnline(),
            ],
            'is_owner' => $isOwner,
            'is_following' => $isFollowing,
            'follower_count' => $followerCount,
            'following_count' => $followingCount,
            'auction_count' => $user->auctions()->count(),
            'bid_count' => $user->bids()->count(),
            'is_seller' => $user->isSeller(),
            'rating_fmt' => $user->isSeller() ? number_format($user->sellerRating(), 1) : '—',
            'is_creator_seller' => $user->isSeller(),
            'showcase' => $showcase,
            'activities' => $activities->map(fn ($act) => [
                'icon' => $act['icon'],
                'color' => $act['color'],
                'title' => $act['title'],
                'subject' => \Illuminate\Support\Str::limit($act['subject'], 52),
                'amount_fmt' => $fmt($act['amount']),
                'date' => $act['date']?->diffForHumans(),
                'url' => $act['url'],
            ])->values(),
            'reviews' => $reviews,
            'privacy' => [
                'profile_public' => (bool) $user->profile_public,
                'bids_hidden' => (bool) $user->bids_hidden,
                'show_online' => (bool) $user->show_online,
                'email_notifications' => (bool) $user->email_notifications,
                'messages_followers_only' => (bool) $user->messages_followers_only,
            ],
            'social' => [
                'instagram' => old('social.instagram', $user->social['instagram'] ?? ''),
                'twitter' => old('social.twitter', $user->social['twitter'] ?? ''),
                'youtube' => old('social.youtube', $user->social['youtube'] ?? ''),
                'linkedin' => old('social.linkedin', $user->social['linkedin'] ?? ''),
            ],
            'form' => [
                'name' => old('name', $user->name),
                'username' => old('username', $user->username),
                'phone' => old('phone', $user->phone),
                'bio' => old('bio', $user->bio),
                'email' => old('email', ''),
            ],
            'stories' => [
                'has' => $heroStories->isNotEmpty(),
                'ids' => $heroStories->pluck('id')->values(),
                'ring_unseen' => story_ring_style($heroStories->count()),
                'ring_seen' => story_ring_style($heroStories->count(), true),
                'payload' => [
                    'name' => $user->name,
                    'avatar' => $user->profile_img,
                    'isOwner' => (bool) $isOwner,
                    'profile_url' => $user->username ? route('profile.public', $user->username) : null,
                    'items' => $heroStories->map(fn ($st) => [
                        'id' => $st->id,
                        'type' => $st->media_type,
                        'url' => $st->url(),
                        'caption' => $st->caption,
                    ])->values(),
                ],
            ],
            'urls' => [
                'followers' => route('profile.followers', $user->username),
                'following' => route('profile.following', $user->username),
                'follow_toggle' => route('follow.toggle', $user),
                'messages_start' => route('messages.start'),
                'public' => route('profile.public', $user->username),
                'update' => route('profile.update'),
                'email' => route('profile.email'),
                'password' => route('profile.password'),
                'privacy' => route('profile.privacy'),
                'social' => route('profile.social'),
                'destroy' => route('profile.destroy'),
                'seller_create' => \Illuminate\Support\Facades\Route::has('seller.auctions.create') ? route('seller.auctions.create') : '#',
                'browse' => route('browse.auctions'),
                'login' => route('login'),
            ],
            'flash' => [
                'profile_success' => session('profile_success'),
                'email_success' => session('email_success'),
                'password_success' => session('password_success'),
                'status' => session('status'),
                'error' => session('error'),
            ],
            'errors_flat' => $errorFlat,
        ];

        $config = [
            'public_url' => route('profile.public', $user->username),
            'drawer_open' => $psDrawerOpen ? '1' : '0',
            'drawer_tab' => $psDrawerTab,
            'drawer_inline' => $psDrawerInline,
            'error_fields' => $errorFields,
            'csrf' => csrf_token(),
        ];

        return \Inertia\Inertia::render('Profile/Show', ['pf' => $pf, 'config' => $config]);
    }

    private function buildActivities(User $user, bool $isOwner)
    {
        $items = collect();

        $canSeeBids = $isOwner || ! $user->bids_hidden;

        if ($canSeeBids) {
            foreach ($user->bids()->with('auction')->latest()->take(10)->get() as $bid) {
                $items->push([
                    'type'   => 'bid',
                    'icon'   => 'bi-hammer',
                    'color'  => '#155eef',
                    'title'  => $isOwner ? 'Teklif verdin' : 'Teklif verdi',
                    'subject'=> $bid->auction?->title ?? 'İlan silinmiş',
                    'amount' => $bid->amount,
                    'date'   => $bid->created_at,
                    'url'    => $bid->auction ? route('auctions.show', $bid->auction) : null,
                ]);
            }
        }

        foreach ($user->purchases()->with('auction')->latest()->take(10)->get() as $order) {
            $items->push([
                'type'   => 'win',
                'icon'   => 'bi-trophy-fill',
                'color'  => '#f59e0b',
                'title'  => $isOwner ? 'Açık artırmayı kazandın' : 'Açık artırmayı kazandı',
                'subject'=> $order->auction?->title ?? 'İlan silinmiş',
                'amount' => $order->amount,
                'date'   => $order->created_at,
                'url'    => $order->auction ? route('auctions.show', $order->auction) : null,
            ]);
        }

        return $items->sortByDesc('date')->take(12)->values();
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_.]+$/',
                'unique:users,username,'.$user->id,
            ],
            'phone' => ['required', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:300'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Ad Soyad zorunludur.',
            'username.required' => 'Kullanıcı adı zorunludur.',
            'username.min' => 'Kullanıcı adı en az 3 karakter olmalı.',
            'username.max' => 'Kullanıcı adı en fazla 30 karakter olabilir.',
            'username.unique' => 'Bu kullanıcı adı zaten alınmış.',
            'username.regex' => 'Sadece harf, rakam, nokta ve alt çizgi kullanılabilir.',
            'phone.required' => 'Telefon zorunludur.',
            'bio.max' => 'Bio en fazla 300 karakter olabilir.',
        ]);

        $user->name = $request->name;
        $user->username = Str::lower($request->username);
        $user->phone = $request->phone;
        $user->bio = $request->bio;

        if ($request->hasFile('profile_image')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('profile_image')->store('avatars', 'public');
        }

        $user->save();

        return back()->with('profile_success', 'Profil bilgileriniz güncellendi.');
    }

    public function email(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'confirmemailpassword' => ['required'],
        ], [
            'email.required' => 'E-posta zorunludur.',
            'email.unique' => 'Bu e-posta zaten kullanılıyor.',
            'confirmemailpassword.required' => 'Mevcut şifrenizi girin.',
        ]);

        if (! Hash::check($request->confirmemailpassword, $user->password)) {
            return back()->withErrors(['confirmemailpassword' => 'Şifreniz hatalı.']);
        }

        $user->email = $request->email;
        $user->email_verified_at = null;
        $user->save();

        return back()->with('email_success', 'E-posta adresiniz güncellendi.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'currentpassword' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'currentpassword.required' => 'Mevcut şifrenizi girin.',
            'password.required' => 'Yeni şifre zorunludur.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->currentpassword, $user->password)) {
            return back()->withErrors(['currentpassword' => 'Mevcut şifreniz hatalı.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('password_success', 'Şifreniz başarıyla güncellendi.');
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();

        if ($user->password) {
            $request->validate([
                'delete_password' => ['required'],
            ], [
                'delete_password.required' => 'Hesabı silmek için şifrenizi girin.',
            ]);

            if (! Hash::check($request->delete_password, $user->password)) {
                return back()->withErrors(['delete_password' => 'Şifreniz hatalı.']);
            }
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        auth()->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Hesabınız kalıcı olarak silindi.');
    }

    public function show(string $username)
    {
        $username = ltrim($username, '@');
        $user = User::with('roles')
            ->where('username', strtolower($username))
            ->firstOrFail();

        return $this->renderProfile($user);
    }
}
