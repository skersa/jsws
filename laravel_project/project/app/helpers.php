<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    function setting(string|array $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Setting::set($k, $v);
            }
            return null;
        }

        return Setting::get($key, $default);
    }
}

if (!function_exists('story_ring_style')) {
    /**
     * Instagram tarzı segmentli hikaye halkası için conic-gradient stili üretir.
     * Segment sayısı = hikaye sayısı; segmentler arasında küçük boşluklar bulunur.
     */
    function story_ring_style(int $count, bool $seen = false): string
    {
        $count = max(1, $count);
        $c1 = $seen ? '#4b5563' : '#155eef';
        $c2 = $seen ? '#6b7280' : '#00d4ff';

        if ($count === 1) {
            return "background: linear-gradient(135deg, {$c1}, {$c2});";
        }

        $gap  = $count > 12 ? 3 : 8;               // segmentler arası boşluk (derece)
        $each = 360 / $count;
        $fill = max(4, $each - $gap);

        $stops = [];
        for ($i = 0; $i < $count; $i++) {
            $start = round($i * $each, 2);
            $end   = round($start + $fill, 2);
            $next  = round(($i + 1) * $each, 2);
            $color = $i % 2 === 0 ? $c1 : $c2;
            $stops[] = "{$color} {$start}deg {$end}deg";
            $stops[] = "transparent {$end}deg {$next}deg";
        }

        return 'background: conic-gradient(from -90deg, ' . implode(', ', $stops) . ');';
    }
}


if (!function_exists('bid_row')) {
    /** Teklif satırı (dashboard / my-bids) için serialize */
    function bid_row($bid): array
    {
        $a = $bid->auction;
        return [
            'id'          => $bid->id,
            'title'       => \Illuminate\Support\Str::limit($a?->title ?? 'İlan silinmiş', 40),
            'cover_url'   => $a?->coverUrl() ?? asset('assets/media/placeholder.svg'),
            'show_url'    => $a ? route('auctions.show', $a->slug) : '#',
            'amount'      => number_format($bid->amount, 0, ',', '.') . ' ₺',
            'status'      => $a?->status,
            'created_at'  => $bid->created_at->diffForHumans(),
        ];
    }
}

if (!function_exists('story_bar_data')) {
    /**
     * Story-bar (Inertia/Vue) için serialize edilmiş hikaye verisi döndürür.
     * Blade'deki partials/story-bar mantığının birebir karşılığı.
     */
    function story_bar_data(): array
    {
        \App\Models\Story::pruneExpired();

        $storyUsers = \App\Models\User::whereHas('stories', fn ($q) => $q->where('expires_at', '>', now()))
            ->with(['stories' => fn ($q) => $q->where('expires_at', '>', now())->orderBy('id')])
            ->take(25)->get();

        if (auth()->check()) {
            $me = $storyUsers->firstWhere('id', auth()->id());
            if ($me) {
                $storyUsers = $storyUsers->reject(fn ($u) => $u->id === auth()->id())->prepend($me)->values();
            }
        }

        return $storyUsers->map(function ($su) {
            $ids = $su->stories->pluck('id')->values();
            return [
                'id'          => $su->id,
                'name'        => $su->name,
                'name_short'  => \Illuminate\Support\Str::limit($su->name, 10),
                'avatar'      => $su->profile_img,
                'story_ids'   => $ids,
                'ring_style'  => story_ring_style($su->stories->count()),
                'ring_unseen' => story_ring_style($su->stories->count()),
                'ring_seen'   => story_ring_style($su->stories->count(), true),
                'payload'     => [
                    'name'    => $su->id === auth()->id() ? 'Hikayen' : $su->name,
                    'avatar'  => $su->profile_img,
                    'isOwner' => auth()->id() === $su->id,
                    'profile_url' => $su->username ? route('profile.public', $su->username) : null,
                    'items'   => $su->stories->map(fn ($st) => [
                        'id'      => $st->id,
                        'type'    => $st->media_type,
                        'url'     => $st->url(),
                        'caption' => $st->caption,
                    ])->values(),
                ],
            ];
        })->toArray();
    }
}

