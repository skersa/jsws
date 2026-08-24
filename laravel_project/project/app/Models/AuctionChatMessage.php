<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionChatMessage extends Model
{
    protected $fillable = [
        'auction_id', 'user_id', 'message', 'is_seller',
    ];

    protected $casts = [
        'is_seller' => 'boolean',
    ];

    public function auction() { return $this->belongsTo(Auction::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
