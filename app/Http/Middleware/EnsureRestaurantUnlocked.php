<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurant = $request->route('restaurant');

        if ($restaurant === null) {
            abort(404);
        }

        if (! $restaurant instanceof Restaurant) {
            $restaurant = Restaurant::query()->find($restaurant);
        }

        if (! $restaurant instanceof Restaurant) {
            abort(404);
        }

        if (($restaurant->access_status ?? 'active') === 'locked') {
            return redirect()
                ->route('mitra.dashboard')
                ->withErrors([
                    'access' => 'Akses ke restoran ini ditahan admin. Kelola restoran tidak tersedia hingga akses dibuka kembali.',
                ]);
        }

        $restaurantId = $restaurant->id;

        $sessionKey = 'unlocked_restaurant_'.$restaurantId;

        if (! $request->session()->has($sessionKey) || $request->session()->get($sessionKey) !== true) {
            return redirect()
                ->route('mitra.restaurants.unlock.form', ['restaurant' => $restaurantId])
                ->withErrors(['pin' => 'Silakan masukkan PIN untuk mengelola restoran ini.']);
        }

        return $next($request);
    }
}
