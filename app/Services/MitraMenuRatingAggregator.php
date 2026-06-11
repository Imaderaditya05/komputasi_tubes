<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use App\Models\Menu;
use Illuminate\Support\Facades\Schema;

class MitraMenuRatingAggregator
{
    /**
     * Setelah ulasan disimpan di checkout_orders, tambahkan skor ke mitra_menus (jika slug mitra-menu-*).
     */
    public function applyOrderReview(CheckoutOrder $order): void
    {
        if (! Schema::hasTable('mitra_menus') || ! Schema::hasColumn('mitra_menus', 'avg_rating')) {
            return;
        }

        $slug = (string) ($order->box_slug ?? '');
        if (! str_starts_with($slug, 'mitra-menu-')) {
            return;
        }

        $menuId = (int) substr($slug, strlen('mitra-menu-'));
        if ($menuId < 1) {
            return;
        }

        $rating = (int) ($order->customer_rating ?? 0);
        if ($rating < 1 || $rating > 5) {
            return;
        }

        $menu = Menu::query()->find($menuId);
        if (! $menu) {
            return;
        }

        $prevCount = (int) ($menu->ratings_count ?? 0);
        $prevAvg = $menu->avg_rating !== null ? (float) $menu->avg_rating : null;

        $newCount = $prevCount + 1;
        $newAvg = $prevCount > 0 && $prevAvg !== null
            ? (($prevAvg * $prevCount) + $rating) / $newCount
            : (float) $rating;

        $menu->update([
            'avg_rating' => round($newAvg, 2),
            'ratings_count' => $newCount,
        ]);
    }
}
