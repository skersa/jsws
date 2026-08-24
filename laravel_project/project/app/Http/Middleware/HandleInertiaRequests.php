<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'username'   => $user->username,
                    'avatar'     => $user->avatar ? asset('storage/' . $user->avatar) : null,
                    'avatar_char' => mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1)),
                    'balance'    => (float) ($user->balance ?? 0),
                    'roles'      => $user->getRoleNames()->toArray(),
                    'is_admin'   => $user->isAdmin(),
                    'is_seller'  => $user->isSeller(),
                    'is_buyer'   => method_exists($user, 'isBuyer') ? $user->isBuyer() : $user->hasRole('buyer'),
                    'unread_messages'      => $user->unreadMessagesCount(),
                    'unread_notifications' => $user->unreadNotifications->count(),
                    'seller_live_badge'    => $user->isSeller()
                        ? $user->auctions()->where('status', 'active')->where('stream_mode', 'live')->count()
                        : 0,
                ] : null,
            ],
            'headerNotifications' => $user ? $this->headerNotifications($user) : [],
            'csrf_token' => csrf_token(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'status'  => fn () => $request->session()->get('status'),
                'message' => fn () => $request->session()->get('message'),
                'contact_success' => fn () => $request->session()->get('contact_success'),
                'profile_section' => fn () => $request->session()->get('profile_section'),
                'profile_success' => fn () => $request->session()->get('profile_success'),
                'category_success' => fn () => $request->session()->get('category_success'),
                'settings_success' => fn () => $request->session()->get('settings_success'),
                'settings_error'   => fn () => $request->session()->get('settings_error'),
            ],
            'ziggy' => fn () => array_merge((new Ziggy)->toArray(), [
                'location' => $request->url(),
            ]),
        ]);
    }

    protected function headerNotifications($user): array
    {
        return $user->notifications()->latest()->take(6)->get()->map(function ($notif) {
            $data   = $notif->data;
            $type   = $data['type'] ?? 'follow';
            $unread = is_null($notif->read_at);

            $meta = match ($type) {
                'follow'            => ['bi-person-plus-fill', '#155eef'],
                'new_bid'           => ['bi-currency-lira', '#10b981'],
                'auction_approved'  => ['bi-check-circle-fill', '#22c55e'],
                'auction_rejected'  => ['bi-x-circle-fill', '#ef4444'],
                'auction_ended'     => ['bi-flag-fill', '#6b7280'],
                'buy_now'           => ['bi-lightning-fill', '#f59e0b'],
                default             => ['bi-bell-fill', '#155eef'],
            };
            [$icon, $color] = $meta;

            $avatarName = $data['follower_name'] ?? $data['bidder_name'] ?? $data['buyer_name'] ?? null;
            $avatarImg  = $data['follower_avatar'] ?? $data['bidder_avatar'] ?? $data['buyer_avatar'] ?? null;
            $avatarUser = $data['follower_username'] ?? $data['bidder_username'] ?? $data['buyer_username'] ?? null;

            $link = match ($type) {
                'follow' => $avatarUser ? route('profile.public', $avatarUser) : '#',
                'new_bid', 'auction_approved', 'auction_rejected', 'auction_ended', 'buy_now'
                    => isset($data['auction_slug']) ? route('seller.auctions.show', $data['auction_slug']) : '#',
                default => '#',
            };

            return [
                'id'          => $notif->id,
                'type'        => $type,
                'unread'      => $unread,
                'icon'        => $icon,
                'color'       => $color,
                'message'     => $data['message'] ?? '',
                'link'        => $link,
                'avatar_img'  => $avatarImg,
                'avatar_name' => $avatarName,
                'avatar_char' => $avatarName ? mb_strtoupper(mb_substr($avatarName, 0, 1)) : null,
                'time'        => $notif->created_at->diffForHumans(),
            ];
        })->toArray();
    }
}
