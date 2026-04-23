<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RestaurantAccessController extends Controller
{
    public function showUnlockForm(Request $request, Restaurant $restaurant): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        return view('mitra.restaurants.unlock', compact('restaurant'));
    }

    public function unlock(Request $request, Restaurant $restaurant): RedirectResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $request->validate([
            'pin' => 'required|string',
        ]);

        if (Hash::check($request->pin, $restaurant->pin)) {
            $request->session()->put('unlocked_restaurant_' . $restaurant->id, true);
            return redirect()->route('mitra.restaurants.manage', $restaurant);
        }

        return back()->withErrors(['pin' => 'PIN salah!']);
    }

    public function lock(Request $request, Restaurant $restaurant): RedirectResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $request->session()->forget('unlocked_restaurant_' . $restaurant->id);

        return redirect()->route('mitra.dashboard')->with('status', 'Restoran berhasil dikunci.');
    }
}
