<?php

namespace App\Services;

use App\Models\AdminRestaurant;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CatalogRepository
{
    /**
     * Sidik jari katalog untuk polling (mitra + admin) — ubah saat menu/box diperbarui.
     */
    public function catalogFingerprint(): string
    {
        $parts = [];
        if (Schema::hasTable('mitra_menus')) {
            $parts[] = (string) Menu::query()->max('updated_at');
            $parts[] = (string) Menu::query()->count();
            if (Schema::hasColumn('mitra_menus', 'ratings_count')) {
                $parts[] = (string) Menu::query()->sum('ratings_count');
            }
        }
        if (Schema::hasTable('mitra_restaurants')) {
            $parts[] = (string) Restaurant::query()->max('updated_at');
        }
        if (Schema::hasTable('admin_restaurants')) {
            $parts[] = (string) AdminRestaurant::query()->max('updated_at');
        }

        return md5(implode('|', $parts));
    }

    /**
     * Katalog untuk home/browse/box — dari database admin jika ada, selain itu bawaan.
     * Menu mystery box mitra (mitra_menus) digabung agar tampil di browse & home.
     *
     * @return array{categories: array, restaurants: array, boxes: array}
     */
    public function getCatalog(): array
    {
        if (Schema::hasTable('admin_restaurants') && AdminRestaurant::query()->exists()) {
            $base = $this->fromDatabase();
        } else {
            $base = $this->getBuiltin();
        }

        return $this->appendMitraCatalog($base);
    }

    /**
     * @param  array{categories: array, restaurants: array, boxes: array}  $catalog
     * @return array{categories: array, restaurants: array, boxes: array}
     */
    private function appendMitraCatalog(array $catalog): array
    {
        if (! Schema::hasTable('mitra_menus') || ! Schema::hasTable('mitra_restaurants')) {
            return $catalog;
        }

        $menus = Menu::query()
            ->with('restaurant')
            ->orderBy('id')
            ->get();

        if ($menus->isEmpty()) {
            return $catalog;
        }

        $byRestaurant = $menus->groupBy('restaurant_id');

        foreach ($byRestaurant as $menusGroup) {
            $restaurant = $menusGroup->first()?->restaurant;
            if (! $restaurant instanceof Restaurant) {
                continue;
            }
            if (Schema::hasColumn('mitra_restaurants', 'access_status')
                && ($restaurant->access_status ?? 'active') !== 'active') {
                continue;
            }
            $catalog['restaurants'][] = $this->mitraRestaurantToRow($restaurant, $menusGroup->count(), $menusGroup);
        }

        foreach ($menus as $menu) {
            $restaurant = $menu->restaurant;
            if ($restaurant instanceof Restaurant
                && Schema::hasColumn('mitra_restaurants', 'access_status')
                && ($restaurant->access_status ?? 'active') !== 'active') {
                continue;
            }
            $catalog['boxes'][] = $this->mitraMenuToBox($menu);
        }

        return $catalog;
    }

    /**
     * @param  Collection<int, Menu>  $menusGroup
     */
    private function mitraRestaurantToRow(Restaurant $restaurant, int $boxCount, $menusGroup): array
    {
        $cover = $menusGroup->first(fn (Menu $m) => filled($m->image_url));

        $lat = $restaurant->latitude;
        $lng = $restaurant->longitude;
        $mapParts = array_filter([
            $restaurant->name,
            $restaurant->address_line,
            'Indonesia',
        ], fn ($v) => filled($v));

        return [
            'id' => 'mitra-'.$restaurant->id,
            'name' => $restaurant->name,
            'area' => '',
            'city' => '',
            'rating' => $this->mitraRestaurantWeightedRating($menusGroup),
            'tags' => [],
            'subtitle' => $restaurant->description ?? '',
            'image' => $cover?->image_url ?? '',
            'boxes_available' => $boxCount,
            'latitude' => $lat !== null ? (float) $lat : null,
            'longitude' => $lng !== null ? (float) $lng : null,
            'map_query' => count($mapParts) ? implode(', ', $mapParts) : $restaurant->name.', Indonesia',
        ];
    }

    private function mitraMenuToBox(Menu $menu): array
    {
        $filterKey = $this->normalizeMitraFilterKey($menu->category);
        $catLabel = filled($menu->category) ? $menu->category : 'Mystery';
        $avg = $menu->avg_rating;
        $rCount = (int) ($menu->ratings_count ?? 0);
        $cardRating = $avg !== null ? (float) $avg : 4.6;

        return [
            'slug' => 'mitra-menu-'.$menu->id,
            'title' => $menu->name,
            'restaurant_id' => 'mitra-'.$menu->restaurant_id,
            'category' => $filterKey,
            'category_label' => $catLabel,
            'filter_key' => $filterKey,
            'card_rating' => $cardRating,
            'ratings_count' => $rCount,
            'image' => $menu->image_url ?: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
            'price' => (int) $menu->price,
            'original_price' => (int) max((float) $menu->original_price, (float) $menu->price),
            'pickup_time' => $menu->pickup_time ?: '—',
            'badge' => 'Mitra',
            'distance_km' => 2.5,
            'stock' => (int) $menu->stock,
            'description' => $menu->description ?? '',
            'highlights' => [],
        ];
    }

    private function normalizeMitraFilterKey(?string $cat): string
    {
        $c = strtolower(trim((string) $cat));
        if ($c === '') {
            return 'restaurant';
        }
        if (str_contains($c, 'bakery') || str_contains($c, 'roti')) {
            return 'bakery';
        }
        if (str_contains($c, 'italian') || str_contains($c, 'pizza')) {
            return 'italian';
        }
        if (str_contains($c, 'japan') || str_contains($c, 'sushi')) {
            return 'japanese';
        }
        if (str_contains($c, 'cafe') || str_contains($c, 'coffee') || str_contains($c, 'kopi')) {
            return 'cafe';
        }
        if (str_contains($c, 'healthy') || str_contains($c, 'salad')) {
            return 'healthy';
        }

        return 'restaurant';
    }

    /**
     * Rating toko mitra = rata-rata tertimbang dari semua ulasan per menu.
     *
     * @param  Collection<int, Menu>  $menusGroup
     */
    private function mitraRestaurantWeightedRating(Collection $menusGroup): float
    {
        $sumWeighted = 0.0;
        $sumCount = 0;
        foreach ($menusGroup as $m) {
            $c = (int) ($m->ratings_count ?? 0);
            if ($c > 0 && $m->avg_rating !== null) {
                $sumWeighted += (float) $m->avg_rating * $c;
                $sumCount += $c;
            }
        }

        if ($sumCount === 0) {
            return 4.6;
        }

        return round($sumWeighted / $sumCount, 2);
    }

    /**
     * @return array{categories: array, restaurants: array, boxes: array}
     */
    public function getBuiltin(): array
    {
        $categories = [
            ['id' => 'bakery', 'name' => 'Bakery', 'icon' => '🥐'],
            ['id' => 'rice', 'name' => 'Rice Bowl', 'icon' => '🍚'],
            ['id' => 'noodles', 'name' => 'Noodles', 'icon' => '🍜'],
            ['id' => 'salad', 'name' => 'Healthy', 'icon' => '🥗'],
            ['id' => 'drinks', 'name' => 'Drinks', 'icon' => '🧋'],
            ['id' => 'cafe', 'name' => 'Cafe', 'icon' => '☕'],
            ['id' => 'italian', 'name' => 'Italian', 'icon' => '🍕'],
            ['id' => 'japanese', 'name' => 'Japanese', 'icon' => '🍣'],
        ];

        $restaurants = [
            [
                'id' => 'sunrise-bakery',
                'name' => 'Sunrise Bakery',
                'area' => 'Senopati',
                'city' => 'Jakarta',
                'rating' => 4.8,
                'tags' => ['bakery'],
                'subtitle' => 'Premium bakery serving fresh artisan bread and pastries daily. Known for our sourdough and croissants.',
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
                'boxes_available' => 1,
                'latitude' => -6.227,
                'longitude' => 106.8088,
                'map_query' => 'Sunrise Bakery, Senopati, Jakarta Selatan, Indonesia',
            ],
            [
                'id' => 'noodle-house',
                'name' => 'Noodle House',
                'area' => 'Kemang',
                'city' => 'Jakarta',
                'rating' => 4.6,
                'tags' => ['noodles', 'rice'],
                'subtitle' => 'Authentic Asian noodle restaurant with variety of soup and dry noodle dishes.',
                'image' => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=800&q=80',
                'boxes_available' => 1,
                'latitude' => -6.2605,
                'longitude' => 106.8102,
                'map_query' => 'Noodle House, Kemang, Jakarta Selatan, Indonesia',
            ],
            [
                'id' => 'urban-coffee',
                'name' => 'Urban Coffee & Bites',
                'area' => 'Kemang',
                'city' => 'Jakarta',
                'rating' => 4.7,
                'tags' => ['drinks', 'cafe'],
                'subtitle' => 'Specialty coffee, pastries, and light bites in a cozy urban setting.',
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
                'boxes_available' => 1,
                'latitude' => -6.2582,
                'longitude' => 106.8115,
                'map_query' => 'Urban Coffee & Bites, Kemang, Jakarta Selatan, Indonesia',
            ],
            [
                'id' => 'green-bowl-cafe',
                'name' => 'Green Bowl Cafe',
                'area' => 'Darmo',
                'city' => 'Surabaya',
                'rating' => 4.9,
                'tags' => ['salad', 'drinks'],
                'subtitle' => 'Fresh salads, grain bowls, and cold-pressed juices for a balanced day.',
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80',
                'boxes_available' => 1,
                'latitude' => -7.2853,
                'longitude' => 112.7308,
                'map_query' => 'Green Bowl Cafe, Darmo, Surabaya, Indonesia',
            ],
            [
                'id' => 'sushi-master',
                'name' => 'Sushi Master',
                'area' => 'SCBD',
                'city' => 'Jakarta',
                'rating' => 4.8,
                'tags' => ['rice', 'drinks', 'japanese'],
                'subtitle' => 'Japanese cuisine with premium fish and creative rolls for dinner crowds.',
                'image' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=800&q=80',
                'boxes_available' => 1,
                'latitude' => -6.2255,
                'longitude' => 106.8084,
                'map_query' => 'Sushi Master, SCBD, Jakarta Selatan, Indonesia',
            ],
            [
                'id' => 'pizza-paradise',
                'name' => 'Pizza Paradise',
                'area' => 'Cihampelas',
                'city' => 'Bandung',
                'rating' => 4.5,
                'tags' => ['bakery', 'rice', 'italian'],
                'subtitle' => 'Wood-fired pizzas and Italian comfort food with local twists.',
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
                'boxes_available' => 1,
                'latitude' => -6.8892,
                'longitude' => 107.5964,
                'map_query' => 'Pizza Paradise, Cihampelas, Bandung, Indonesia',
            ],
            [
                'id' => 'warung-sari-rasa',
                'name' => 'Warung Sari Rasa',
                'area' => 'Malioboro',
                'city' => 'Yogyakarta',
                'rating' => 4.4,
                'tags' => ['rice', 'noodles'],
                'subtitle' => 'Home-style Indonesian plates — perfect for mystery rice & side combos.',
                'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80',
                'boxes_available' => 0,
                'latitude' => -7.7956,
                'longitude' => 110.3654,
                'map_query' => 'Warung Sari Rasa, Malioboro, Yogyakarta, Indonesia',
            ],
        ];

        $boxes = [
            [
                'slug' => 'bakery-surprise-box',
                'title' => 'Bakery Surprise Box',
                'restaurant_id' => 'sunrise-bakery',
                'category' => 'bakery',
                'category_label' => 'Bakery',
                'filter_key' => 'bakery',
                'card_rating' => 4.8,
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
                'price' => 25000,
                'original_price' => 80000,
                'pickup_time' => '20:00 - 21:00',
                'badge' => 'Hot',
                'distance_km' => 1.2,
                'stock' => 12,
                'description' => 'Kombinasi roti & pastry yang masih layak konsumsi. Isi bervariasi tiap hari (surprise!).',
                'highlights' => [
                    'Fresh hari ini (sisa produksi)',
                    'Dikemas higienis',
                    'Harga hemat, dampak besar',
                ],
            ],
            [
                'slug' => 'cafe-mystery-box',
                'title' => 'Cafe Mystery Box',
                'restaurant_id' => 'urban-coffee',
                'category' => 'cafe',
                'category_label' => 'Cafe',
                'filter_key' => 'cafe',
                'card_rating' => 4.7,
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
                'price' => 28000,
                'original_price' => 85000,
                'pickup_time' => '20:30 - 21:30',
                'badge' => 'New',
                'distance_km' => 1.8,
                'stock' => 10,
                'description' => 'Pastry, sandwich mini, dan minuman surprise dari sisa stok layak konsumsi hari ini.',
                'highlights' => ['Perfect for coffee lovers', 'Campuran sweet & savory'],
            ],
            [
                'slug' => 'sushi-mystery-box',
                'title' => 'Sushi Mystery Box',
                'restaurant_id' => 'sushi-master',
                'category' => 'japanese',
                'category_label' => 'Japanese',
                'filter_key' => 'japanese',
                'card_rating' => 4.8,
                'image' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=800&q=80',
                'price' => 40000,
                'original_price' => 120000,
                'pickup_time' => '20:00 - 21:00',
                'badge' => 'Premium',
                'distance_km' => 2.2,
                'stock' => 2,
                'description' => 'Pilihan roll & sashimi sisa layak konsumsi dari dapur — surprise setiap hari.',
                'highlights' => ['Ikan segar', 'Standar higiene tinggi'],
            ],
            [
                'slug' => 'noodle-mystery-box',
                'title' => 'Restaurant Mystery Box',
                'restaurant_id' => 'noodle-house',
                'category' => 'noodles',
                'category_label' => 'Restaurant',
                'filter_key' => 'restaurant',
                'card_rating' => 4.6,
                'image' => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=800&q=80',
                'price' => 30000,
                'original_price' => 90000,
                'pickup_time' => '21:00 - 22:00',
                'badge' => 'Value',
                'distance_km' => 2.5,
                'stock' => 3,
                'description' => 'Mie + side menu pilihan chef (makanan sisa layak konsumsi yang tersisa hari itu).',
                'highlights' => ['Porsi kenyang', 'Bumbu khas', 'Dukung pengurangan food waste'],
            ],
            [
                'slug' => 'healthy-green-box',
                'title' => 'Healthy Surprise Box',
                'restaurant_id' => 'green-bowl-cafe',
                'category' => 'salad',
                'category_label' => 'Healthy',
                'filter_key' => 'healthy',
                'card_rating' => 4.9,
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80',
                'price' => 20000,
                'original_price' => 70000,
                'pickup_time' => '19:30 - 20:30',
                'badge' => 'Fresh',
                'distance_km' => 3.1,
                'stock' => 20,
                'description' => 'Salad/healthy bowl yang tersisa dari batch harian, masih segar dan layak konsumsi.',
                'highlights' => ['Segar', 'Topping bervariasi', 'Lebih ramah bumi'],
            ],
            [
                'slug' => 'pizza-surprise-box',
                'title' => 'Pizza Surprise Box',
                'restaurant_id' => 'pizza-paradise',
                'category' => 'italian',
                'category_label' => 'Italian',
                'filter_key' => 'italian',
                'card_rating' => 4.5,
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
                'price' => 35000,
                'original_price' => 100000,
                'pickup_time' => '21:00 - 22:00',
                'badge' => 'Hot',
                'distance_km' => 5.2,
                'stock' => 15,
                'description' => 'Slice & side Italian surprise dari oven kayu — sisa layak konsumsi dengan harga hemat.',
                'highlights' => ['Keju melt', 'Crust wood-fired'],
            ],
        ];

        return compact('categories', 'restaurants', 'boxes');
    }

    /**
     * @return array{categories: array, restaurants: array, boxes: array}
     */
    public function fromDatabase(): array
    {
        $builtin = $this->getBuiltin();
        $categories = $builtin['categories'];
        $restaurants = [];
        $boxes = [];

        foreach (AdminRestaurant::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get() as $ar) {
            $boxList = is_array($ar->boxes_json) ? $ar->boxes_json : [];
            $mapQuery = trim(implode(', ', array_filter([
                $ar->name,
                $ar->address_line ?? null,
                $ar->area ?? null,
                $ar->city ?? null,
            ]))).', Indonesia';
            $restaurants[] = [
                'id' => $ar->slug,
                'name' => $ar->name,
                'area' => $ar->area ?? '',
                'city' => $ar->city ?? '',
                'rating' => (float) $ar->rating,
                'tags' => [],
                'subtitle' => $ar->description ?? '',
                'image' => $ar->image_url ?? '',
                'boxes_available' => count($boxList),
                'latitude' => $ar->latitude !== null ? (float) $ar->latitude : null,
                'longitude' => $ar->longitude !== null ? (float) $ar->longitude : null,
                'map_query' => $mapQuery,
            ];
            foreach ($boxList as $box) {
                if (is_array($box)) {
                    $boxes[] = $box;
                }
            }
        }

        return compact('categories', 'restaurants', 'boxes');
    }

    /**
     * Data restoran untuk satu mystery box (mis. koordinat peta).
     *
     * @return array<string, mixed>|null
     */
    public function getRestaurantForBoxSlug(string $boxSlug): ?array
    {
        $catalog = $this->getCatalog();
        foreach ($catalog['boxes'] as $box) {
            if (($box['slug'] ?? '') === $boxSlug) {
                foreach ($catalog['restaurants'] as $r) {
                    if (($r['id'] ?? '') === ($box['restaurant_id'] ?? '')) {
                        return $r;
                    }
                }
            }
        }

        return null;
    }

    public function catalogHasRestaurantId(string $id): bool
    {
        foreach ($this->getCatalog()['restaurants'] as $r) {
            if ((string) ($r['id'] ?? '') === $id) {
                return true;
            }
        }

        return false;
    }

    public function catalogHasBoxSlug(string $slug): bool
    {
        foreach ($this->getCatalog()['boxes'] as $box) {
            if (($box['slug'] ?? '') === $slug) {
                return true;
            }
        }

        return false;
    }
}
