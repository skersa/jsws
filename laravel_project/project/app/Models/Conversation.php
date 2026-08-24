<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Conversation extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function userOne() { return $this->belongsTo(User::class, 'user_one_id'); }
    public function userTwo() { return $this->belongsTo(User::class, 'user_two_id'); }
    public function messages() { return $this->hasMany(Message::class)->orderBy('id'); }
    public function lastMessage() { return $this->hasOne(Message::class)->latestOfMany(); }

    public function other(User $user): ?User
    {
        return $this->user_one_id === $user->id ? $this->userTwo : $this->userOne;
    }

    public function hasParticipant(User $user): bool
    {
        return in_array($user->id, [$this->user_one_id, $this->user_two_id], true);
    }

    public function scopeForUser(Builder $q, User $user): Builder
    {
        return $q->where(function ($w) use ($user) {
            $w->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id);
        });
    }

    public static function between(User $a, User $b): self
    {
        $one = min($a->id, $b->id);
        $two = max($a->id, $b->id);

        return static::firstOrCreate(
            ['user_one_id' => $one, 'user_two_id' => $two],
            ['last_message_at' => now()]
        );
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
