<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id', 'subject', 'category', 'priority', 'status', 'last_reply_at', 'last_reply_by',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function lastMessage()
    {
        return $this->hasOne(SupportMessage::class, 'ticket_id')->latestOfMany();
    }

    public function isOpen(): bool   { return $this->status === 'open'; }
    public function isClosed(): bool { return $this->status === 'closed'; }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open'        => 'Açık',
            'in_progress' => 'İşlemde',
            'closed'      => 'Kapalı',
            default       => '—',
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'open'        => 'info',
            'in_progress' => 'warning',
            'closed'      => 'danger',
            default       => 'info',
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'low'    => 'Düşük',
            'medium' => 'Orta',
            'high'   => 'Yüksek',
            default  => '—',
        };
    }

    public function priorityBadge(): string
    {
        return match ($this->priority) {
            'low'    => 'success',
            'medium' => 'warning',
            'high'   => 'danger',
            default  => 'info',
        };
    }
}
