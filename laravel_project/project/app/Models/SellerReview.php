<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerReview extends Model
{
    protected $fillable = ['seller_id', 'reviewer_id', 'order_id', 'rating', 'comment'];

    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }
    public function order() { return $this->belongsTo(Order::class); }
}
