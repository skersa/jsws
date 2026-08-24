<?php

namespace App\Models;

use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use App\Models\BalanceTransaction;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\SellerReview;
use App\Models\Story;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; // 👈 Hatanın çözümü için bu satır eklendi!
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable, Searchable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'avatar',
        'is_verified',
        'bio',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
        ];
    }

    public function auctions()
    {
        return $this->hasMany(Auction::class);
    }

    public function isOnline(): bool
    {
        return Cache::has('user-is-online-'.$this->id);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function watchlist()
    {
        return $this->belongsToMany(Auction::class, 'watchlist')->withTimestamps();
    }

    public function purchases()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function sales()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSeller(): bool
    {
        return $this->hasRole('seller');
    }

    public function isBuyer(): bool
    {
        return $this->hasRole('buyer');
    }

    public function isSellerApproved(): bool
    {
        return $this->hasRole('seller')
            && $this->sellerProfile
            && $this->sellerProfile->verification_status === 'approved';
    }

    public function followings()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function isFollowing(int $userId): bool
    {
        return $this->followings()->where('following_id', $userId)->exists();
    }

    public function getProfileImgAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=009ef7&color=fff&size=160';
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format((float) $this->balance, 2, ',', '.') . ' ₺';
    }

    public function hasEnoughBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    // ===== Mesajlaşma =====
    public function conversations()
    {
        return Conversation::where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id);
    }

    public function unreadMessagesCount(): int
    {
        $ids = Conversation::where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        return Message::whereIn('conversation_id', $ids)
            ->where('sender_id', '!=', $this->id)
            ->whereNull('read_at')
            ->count();
    }

    // ===== Satıcı Puanlama =====
    public function reviewsReceived()
    {
        return $this->hasMany(SellerReview::class, 'seller_id');
    }

    public function hasPurchasedFrom(int $sellerId): bool
    {
        return Order::where('buyer_id', $this->id)
            ->where('seller_id', $sellerId)
            ->exists();
    }

    /** Alıcı, satıcıdan TAMAMLANMIŞ (teslim alınmış) bir siparişe sahip mi? Değerlendirme için şart. */
    public function hasCompletedOrderFrom(int $sellerId): bool
    {
        return Order::where('buyer_id', $this->id)
            ->where('seller_id', $sellerId)
            ->where('status', 'completed')
            ->exists();
    }

    // ===== Hikayeler =====
    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function sellerRating(): float
    {
        return round((float) $this->reviewsReceived()->avg('rating'), 1);
    }

    public function sellerReviewCount(): int
    {
        return $this->reviewsReceived()->count();
    }

    public function reviewFrom(?User $reviewer): ?SellerReview
    {
        if (! $reviewer) {
            return null;
        }

        return $this->reviewsReceived()->where('reviewer_id', $reviewer->id)->first();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
