<?php

namespace App\Http\Controllers;

use App\Models\WishlistItem;
use App\Services\CatalogRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request, CatalogRepository $catalogRepository): View
    {
        $user = $request->user();
        $catalog = $catalogRepository->getCatalog();

        $rows = WishlistItem::query()
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get();

        $restaurants = [];
        foreach ($rows->where('item_type', 'restaurant') as $w) {
            foreach ($catalog['restaurants'] as $r) {
                if ((string) ($r['id'] ?? '') === $w->target_key) {
                    $restaurants[] = $r;
                    break;
                }
            }
        }

        $menus = [];
        foreach ($rows->where('item_type', 'menu') as $w) {
            foreach ($catalog['boxes'] as $box) {
                if (($box['slug'] ?? '') === $w->target_key) {
                    $restaurant = null;
                    foreach ($catalog['restaurants'] as $r) {
                        if (($r['id'] ?? '') === ($box['restaurant_id'] ?? '')) {
                            $restaurant = $r;
                            break;
                        }
                    }
                    $menus[] = ['box' => $box, 'restaurant' => $restaurant];
                    break;
                }
            }
        }

        $boxSlugByRestaurantId = [];
        foreach ($catalog['boxes'] as $box) {
            $rid = (string) ($box['restaurant_id'] ?? '');
            if ($rid !== '' && ! isset($boxSlugByRestaurantId[$rid])) {
                $boxSlugByRestaurantId[$rid] = (string) ($box['slug'] ?? '');
            }
        }

        return view('wishlist.index', [
            'restaurants' => $restaurants,
            'menus' => $menus,
            'boxSlugByRestaurantId' => $boxSlugByRestaurantId,
            'money' => fn (int $n) => 'Rp '.number_format($n, 0, ',', '.'),
        ]);
    }

    public function toggle(Request $request, CatalogRepository $catalogRepository): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:restaurant,menu'],
            'key' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $type = $validated['type'];
        $key = $validated['key'];

        if ($type === 'restaurant' && ! $catalogRepository->catalogHasRestaurantId($key)) {
            return back()->withErrors(['wishlist' => 'Restoran tidak ditemukan di katalog.']);
        }

        if ($type === 'menu' && ! $catalogRepository->catalogHasBoxSlug($key)) {
            return back()->withErrors(['wishlist' => 'Menu tidak ditemukan di katalog.']);
        }

        $existing = WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('item_type', $type)
            ->where('target_key', $key)
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', 'Dihapus dari wishlist.');
        }

        WishlistItem::query()->create([
            'user_id' => $user->id,
            'item_type' => $type,
            'target_key' => $key,
        ]);

        return back()->with('status', 'Disimpan ke wishlist.');
    }
}
