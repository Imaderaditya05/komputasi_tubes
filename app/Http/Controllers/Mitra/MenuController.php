<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $menus = $restaurant->menus()->get();
        return view('mitra.menus.index', compact('restaurant', 'menus'));
    }

    public function create(Request $request, Restaurant $restaurant): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        return view('mitra.menus.create', compact('restaurant'));
    }

    public function store(StoreMenuRequest $request, Restaurant $restaurant): RedirectResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $restaurant->menus()->create($request->validated());

        return redirect()->route('restaurants.menus.index', $restaurant)
            ->with('status', 'Menu berhasil ditambahkan.');
    }

    public function edit(Request $request, Restaurant $restaurant, Menu $menu): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');
        abort_unless($menu->restaurant_id === $restaurant->id, 404, 'Not Found');

        return view('mitra.menus.edit', compact('restaurant', 'menu'));
    }

    public function update(StoreMenuRequest $request, Restaurant $restaurant, Menu $menu): RedirectResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');
        abort_unless($menu->restaurant_id === $restaurant->id, 404, 'Not Found');

        $menu->update($request->validated());

        return redirect()->route('restaurants.menus.index', $restaurant)
            ->with('status', 'Menu berhasil diperbarui.');
    }

    public function destroy(Request $request, Restaurant $restaurant, Menu $menu): RedirectResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');
        abort_unless($menu->restaurant_id === $restaurant->id, 404, 'Not Found');

        $menu->delete();

        return redirect()->route('restaurants.menus.index', $restaurant)
            ->with('status', 'Menu berhasil dihapus.');
    }
}
