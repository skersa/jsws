<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $users = User::search($query)->take(5)->get()->map(function($user) {
            return [
                'title'  => $user->name,
                'username'   => $user->username,
                'avatar' => $user->profile_img,
                'url'   => route('profile.public', $user->username)
            ];
        });

        // İstersen ilanları da aynı anda aratıp birleştirebilirsin:
        /*
        $auctions = Auction::search($query)->take(5)->get()->map(function($auction) {
            return [
                'title' => $auction->title,
                'type'  => 'İlan',
                'url'   => route('auctions.show', $auction->id)
            ];
        });
        $results = $users->concat($auctions);
        */

        return response()->json($users);
    }
}
