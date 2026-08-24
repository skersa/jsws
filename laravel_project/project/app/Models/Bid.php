<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = [
        'auction_id', 'user_id', 'amount', 'is_auto', 'max_auto_amount', 'ip_address',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'max_auto_amount' => 'decimal:2',
        'is_auto'         => 'boolean',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}