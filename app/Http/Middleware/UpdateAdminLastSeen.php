<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use function now;

class UpdateAdminLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $admin->update([
                'last_seen_at' => now(),
                'is_online' => true,
            ]);
        }
        return $next($request);
    }
}
