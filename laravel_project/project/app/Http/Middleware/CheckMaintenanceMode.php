<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! setting('maintenance_mode')) {
            return $next($request);
        }

        $user = auth()->user();

        // admin HER ZAMAN geçer
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }

        // admin panel HER ZAMAN açık
        if ($request->is('admin/*')) {
            return $next($request);
        }

        // SADECE PATH kontrol (route name bırak)
        $allowedPaths = [
            '/',
        ];

        foreach ($allowedPaths as $path) {
            if ($request->is($path)) {
                return response()->view('errors.maintenance', [], 503);

            }
        }

        return $next($request);

    }
}
