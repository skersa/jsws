<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;

class AuctionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function broadcast(User $user, Auction $auction): bool
    {
        return $user->id === $auction->user_id;
    }
}
