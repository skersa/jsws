<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;

class Auction extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id','category_id','title','slug','description',
        'starting_price','reserve_price','current_price','buy_now_price',
        'min_bid_increment','starts_at','ends_at','status',
        'is_featured','condition','location',
        'stream_mode','promo_video_url','is_live','live_started_at','live_ended_at',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'starting_price'   => 'decimal:2',
        'current_price'    => 'decimal:2',
        'reserve_price'    => 'decimal:2',
        'buy_now_price'    => 'decimal:2',
        'min_bid_increment'=> 'decimal:2',
        'is_featured'      => 'boolean',
        'is_live'          => 'boolean',
        'live_started_at'  => 'datetime',
        'live_ended_at'    => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            $m->slug         ??= Str::slug($m->title) . '-' . Str::random(5);
            $m->current_price ??= $m->starting_price;
        });
    }

    public function user()     { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function images()   { return $this->hasMany(AuctionImage::class)->orderBy('sort_order'); }
    public function cover()    { return $this->hasOne(AuctionImage::class)->where('is_cover', true); }
    public function bids()     { return $this->hasMany(Bid::class)->latest(); }
    public function chatMessages() { return $this->hasMany(AuctionChatMessage::class); }
    public function watchlist(){ return $this->hasMany(Watchlist::class); }
    public function orders()   { return $this->hasMany(Order::class); }
    public function winningBid(){ return $this->belongsTo(Bid::class, 'winning_bid_id'); }

    public function coverUrl(): string
    {
        return $this->cover?->path
            ? asset('storage/' . $this->cover->path)
            : asset('assets/media/placeholder.svg');
    }

    public function displayPrice(): string
    {
        return number_format($this->current_price, 0, ',', '.') . ' ₺';
    }

    public function timeLeft(): string
    {
        if ($this->ends_at->isPast()) return 'Bitti';
        $diff = now()->diff($this->ends_at);
        if ($diff->days > 0)  return $diff->days . 'g';
        if ($diff->h   > 0)  return $diff->h   . 'sa';
        return $diff->i . 'dk';
    }

    public function isActive(): bool  { return $this->status === 'active' && $this->ends_at->isFuture(); }
    public function isEnding(): bool  { return $this->isActive() && $this->ends_at->diffInHours() < 24; }
    public function bidCount(): int   { return $this->bids()->count(); }

    /** Frontend (Inertia/Vue) kart bileşeni için serialize edilmiş veri */
    public function toCard(): array
    {
        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'title'          => $this->title,
            'cover_url'      => $this->coverUrl(),
            'is_active'      => $this->isActive(),
            'status'         => $this->status,
            'display_price'  => $this->displayPrice(),
            'category_name'  => $this->category?->name,
            'bid_count'      => $this->bids_count ?? $this->bidCount(),
            'location'       => $this->location ? \Illuminate\Support\Str::limit($this->location, 18) : null,
            'time_left'      => $this->timeLeft(),
            'ends_at'        => $this->ends_at?->timestamp,
            'show_url'       => route('auctions.show', $this->slug),
        ];
    }

    /** Yayın/etkileşim tamamen kapandı mı? (socket & polling durdurulur) */
    public function hasFinished(): bool
    {
        return in_array($this->status, ['ended', 'sold', 'cancelled'], true);
    }

    public function usesPromoVideo(): bool
    {
        return $this->stream_mode === 'video' && ! empty($this->promo_video_url);
    }

    /**
     * Tanıtım videosu için gömülebilir (embed) URL üretir.
     * YouTube / Vimeo linklerini iframe embed'e çevirir; diğerlerini olduğu gibi döner (mp4 vb.).
     */
    public function embedVideoUrl(): ?string
    {
        $url = trim((string) $this->promo_video_url);
        if ($url === '') return null;

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        return $url;
    }

    public function isDirectVideoFile(): bool
    {
        return (bool) preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', (string) $this->promo_video_url);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->format('webp')
            ->performOnCollections('images');

        $this->addMediaConversion('card')
            ->width(800)
            ->height(600)
            ->format('webp')
            ->performOnCollections('images');
    }
}
