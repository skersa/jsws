<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Story extends Model
{
    protected $fillable = ['user_id', 'media_path', 'media_type', 'caption', 'view_count', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('expires_at', '>', now());
    }

    public function url(): string
    {
        return asset('storage/' . $this->media_path);
    }

    /**
     * Süresi dolan hikayeleri (medya dosyalarıyla birlikte) fiziksel olarak siler.
     * Cron/scheduler olmayan ortamlarda story-bar render edilirken en fazla
     * dakikada bir çalışacak şekilde throttle edilir.
     */
    public static function pruneExpired(): void
    {
        if (! Cache::add('stories_prune_lock', 1, now()->addMinute())) {
            return;
        }

        static::where('expires_at', '<=', now())->get()->each(function (self $story) {
            if ($story->media_path && Storage::disk('public')->exists($story->media_path)) {
                Storage::disk('public')->delete($story->media_path);
            }
            $story->delete();
        });
    }
}
