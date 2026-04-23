<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurant = $request->route('restaurant');

        if (!$restaurant) {
            abort(404);
        }

        $restaurantId = is_object($restaurant) ? $restaurant->id : $restaurant;
        
        $sessionKey = 'unlocked_restaurant_' . $restaurantId;

        if (!$request->session()->has($sessionKey) || $request->session()->get($sessionKey) !== true) {
            return redirect()
                ->route('mitra.restaurants.unlock.form', ['restaurant' => $restaurantId])
                ->withErrors(['pin' => 'Silakan masukkan PIN untuk mengelola restoran ini.']);
        }

        return $next($request);
    }
}
