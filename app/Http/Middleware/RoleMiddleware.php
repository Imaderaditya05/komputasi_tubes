<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || $request->user()->role !== $role) {
            // Jika role tidak sesuai, misal customer mencoba akses seller
            if ($request->user() && $request->user()->role === 'customer') {
                return redirect()->route('home')->withErrors('Akses ditolak. Anda bukan seller.');
            }
            
            abort(403, 'Akses tidak diizinkan.');
        }

        return $next($request);
    }
}
