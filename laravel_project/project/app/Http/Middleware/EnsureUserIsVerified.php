<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('buyer')) {
            return $next($request);
        }

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if ($user->hasRole('seller')) {
            $profile = $user->sellerProfile;

            if (! $profile) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Hesabınızda bir sorun oluştu, lütfen tekrar kayıt olun.']);
            }

            if ($profile->isPending()) {
                return \Inertia\Inertia::render('Auth/PendingApproval', [
                    'activeAuctions' => \App\Models\Auction::where('status', 'active')->count(),
                ]);
            }

            if ($profile->isRejected()) {
                return response()->view('auth.rejected', [
                    'reason' => $profile->rejection_reason
                ]);
            }
        }

        return $next($request);
    }
}