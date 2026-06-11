<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cegah aksi write ke restoran mitra saat access_status = locked (kunci admin).
 */
class EnsureMitraRestaurantNotAdminLocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurant = $request->route('restaurant');

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
                    'access' => 'Akses restoran ditahan admin — tidak dapat mengubah data.',
                ]);
        }

        return $next($request);
    }
}
