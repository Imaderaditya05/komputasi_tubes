<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function store(StoreRestaurantRequest $request): RedirectResponse
    {
        Restaurant::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'pin' => Hash::make($request->pin),
        ]);

        return redirect()->route('mitra.dashboard')->with('status', 'Restoran berhasil dibuat.');
    }

    public function manage(Request $request, Restaurant $restaurant): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $menusCount = $restaurant->menus()->count();
        $ordersCount = $restaurant->orders()->count();
        $mitraLiveHash = MitraLiveController::fingerprintForRestaurant($restaurant);

        return view('mitra.restaurants.manage', compact('restaurant', 'menusCount', 'ordersCount', 'mitraLiveHash'));
    }
}
