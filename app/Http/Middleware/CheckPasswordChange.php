<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $passwordUpdatedAt = $user->password_updated_at;
            $sessionPasswordUpdatedAt = session('password_updated_at');

            if ($passwordUpdatedAt && $sessionPasswordUpdatedAt && $passwordUpdatedAt->gt($sessionPasswordUpdatedAt)) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('portal/login/')->withErrors(['message' => 'Your password has been changed. Please log in again.']);
            }

            session(['password_updated_at' => $passwordUpdatedAt]);
        }
        return $next($request);
    }
}
