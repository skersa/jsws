<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionImage extends Model
{
    protected $fillable = ['auction_id','path','is_cover','sort_order'];

    public function auction() { return $this->belongsTo(Auction::class); }
    public function url(): string { return asset('storage/' . $this->path); }
}
