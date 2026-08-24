<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

// REMINDER: DO NOT HARDCODE THE URL, OR ADD ANY FALLBACKS OR REDIRECT URLS, THIS BREAKS THE AUTH
class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google ile giriş sırasında bir sorun oluştu. Lütfen tekrar deneyin.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $user = User::create([
                'name'              => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Kullanıcı'),
                'username'          => $this->uniqueUsername($googleUser),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'is_verified'       => true,
                'password'          => null,
            ]);
            $user->assignRole('buyer');
        } elseif (! $user->google_id) {
            $user->google_id = $googleUser->getId();
            if (! $user->avatar) {
                $user->avatar = $googleUser->getAvatar();
            }
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();
        }

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }

    private function uniqueUsername($googleUser): string
    {
        $base = (string) Str::of($googleUser->getNickname() ?: Str::before($googleUser->getEmail(), '@'))
            ->lower()
            ->replaceMatches('/[^a-z0-9_.]/', '');

        if ($base === '') {
            $base = 'kullanici';
        }
        $base = substr($base, 0, 24);

        $username = $base;
        while (User::where('username', $username)->exists()) {
            $username = substr($base, 0, 24) . rand(100, 9999);
        }

        return substr($username, 0, 30);
    }
}
