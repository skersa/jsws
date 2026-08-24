<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * auction.{id} — PresenceChannel
 *
 * Hem satıcı (auctions blade) hem izleyici (auctionsnew blade) bu kanala katılır.
 * Döndürülen dizi Echo presence API'sinde user olarak görünür.
 */
Broadcast::channel('auction.{id}', function ($user, $id) {
    // İsteğe bağlı: sadece ilgili müzayedeye erişimi olan kullanıcılar
    // $auction = \App\Models\Auction::find($id);
    // if (!$auction) return false;

    return [
        'id'   => $user->id,
        'name' => $user->name,
    ];
});
