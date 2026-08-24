<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use App\Notifications\FollowedNotification;

class FollowController extends Controller
{
    public function toggle(User $user)
    {
        $me = auth()->id();

        if ($me === $user->id) {
            return response()->json(['error' => 'Kendinizi takip edemezsiniz.'], 403);
        }

        $existing = Follow::where('follower_id', $me)
            ->where('following_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $following = false;
        } else {
            Follow::create([
                'follower_id' => $me,
                'following_id' => $user->id,
            ]);

            $user->notify(new FollowedNotification(auth()->user()));

            $following = true;
        }

        $followerCount = Follow::where('following_id', $user->id)->count();

        return response()->json([
            'following' => $following,
            'follower_count' => $followerCount,
        ]);
    }

    public function followers(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $followers = Follow::where('following_id', $user->id)->with('follower')->latest()->paginate(24);

        return $this->renderList($user, $followers, 'followers');
    }

    public function following(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $followers = Follow::where('follower_id', $user->id)->with('following')->latest()->paginate(24);

        return $this->renderList($user, $followers, 'following');
    }

    private function renderList(User $user, $followers, string $type)
    {
        $me = auth()->id();
        $meUser = auth()->user();

        $people = collect($followers->items())->map(function ($follow) use ($type, $me, $meUser) {
            $person = $type === 'followers' ? $follow->follower : $follow->following;
            $isSelf = $me === $person->id;

            return [
                'name' => $person->name,
                'username' => $person->username,
                'avatar' => $person->profile_img ?? 'https://ui-avatars.com/api/?name=' . urlencode($person->name) . '&background=155eef&color=fff&size=128',
                'bio' => $person->bio ? \Illuminate\Support\Str::limit($person->bio, 55) : null,
                'profile_url' => route('profile.public', $person->username),
                'follow_url' => route('follow.toggle', $person),
                'is_self' => $isSelf,
                'is_following' => ($meUser && ! $isSelf) ? $meUser->isFollowing($person->id) : false,
            ];
        })->values();

        return \Inertia\Inertia::render('Profile/FollowList', [
            'fl' => [
                'user' => ['name' => $user->name, 'username' => $user->username],
                'type' => $type,
                'follower_count' => Follow::where('following_id', $user->id)->count(),
                'following_count' => Follow::where('follower_id', $user->id)->count(),
                'people' => $people,
                'links' => $followers->onEachSide(1)->linkCollection()->toArray(),
                'has_pages' => $followers->hasPages(),
                'urls' => [
                    'back' => route('profile.public', $user->username),
                    'followers' => route('profile.followers', $user->username),
                    'following' => route('profile.following', $user->username),
                ],
                'csrf' => csrf_token(),
            ],
        ]);
    }
}
