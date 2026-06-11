<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mitra\StoreMysteryBoxRequest;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MysteryBoxController extends Controller
{
    public function store(StoreMysteryBoxRequest $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorizeRestaurant($request, $restaurant);

        $data = $request->validated();
        $data['original_price'] = $data['original_price'] ?? 0;

        $menu = $restaurant->menus()->create($data);

        return response()->json([
            'message' => 'Mystery box berhasil ditambahkan.',
            'menu' => $this->menuPayload($menu->fresh()),
            'stats' => $this->statsPayload($restaurant),
        ]);
    }

    public function update(StoreMysteryBoxRequest $request, Restaurant $restaurant, Menu $menu): JsonResponse
    {
        $this->authorizeRestaurant($request, $restaurant);
        abort_unless($menu->restaurant_id === $restaurant->id, 404);

        $data = $request->validated();
        $data['original_price'] = $data['original_price'] ?? 0;

        $menu->update($data);

        return response()->json([
            'message' => 'Mystery box berhasil diperbarui.',
            'menu' => $this->menuPayload($menu->fresh()),
            'stats' => $this->statsPayload($restaurant),
        ]);
    }

    public function destroy(Request $request, Restaurant $restaurant, Menu $menu): JsonResponse
    {
        $this->authorizeRestaurant($request, $restaurant);
        abort_unless($menu->restaurant_id === $restaurant->id, 404);

        $menu->delete();

        return response()->json([
            'message' => 'Mystery box berhasil dihapus.',
            'stats' => $this->statsPayload($restaurant),
        ]);
    }

    private function authorizeRestaurant(Request $request, Restaurant $restaurant): void
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);
    }

    private function statsPayload(Restaurant $restaurant): array
    {
        $menus = $restaurant->menus()->get();

        return MitraDashboardController::computeStatsStatic($menus);
    }

    private function menuPayload(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'category' => $menu->category,
            'description' => $menu->description,
            'price' => (float) $menu->price,
            'original_price' => (float) $menu->original_price,
            'stock' => (int) $menu->stock,
            'pickup_time' => $menu->pickup_time,
            'image_url' => $menu->image_url,
            'savings_percent' => $menu->savingsPercent(),
            'savings_amount' => $menu->savingsAmount(),
            'avg_rating' => $menu->avg_rating !== null ? (float) $menu->avg_rating : null,
            'ratings_count' => (int) ($menu->ratings_count ?? 0),
        ];
    }
}
