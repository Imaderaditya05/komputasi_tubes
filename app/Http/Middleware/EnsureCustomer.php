<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'customer') {
            return $next($request);
        }

        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'mitra', 'seller'], true)) {
            return redirect()
                ->route('home')
                ->withErrors(['email' => 'Keranjang dan checkout hanya untuk akun pelanggan.']);
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()
            ->route('login')
            ->with('status', 'Silakan daftar atau login sebagai pelanggan untuk melanjutkan checkout.');
    }
}
