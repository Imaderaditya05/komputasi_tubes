<?php

namespace Database\Seeders;

use App\Models\AdminRestaurant;
use App\Services\CatalogRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdminCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('admin_restaurants')) {
            return;
        }

        if (AdminRestaurant::query()->exists()) {
            return;
        }

        $catalog = app(CatalogRepository::class)->getBuiltin();
        $sort = 0;

        foreach ($catalog['restaurants'] as $r) {
            $boxes = [];
            foreach ($catalog['boxes'] as $box) {
                if (($box['restaurant_id'] ?? '') === $r['id']) {
                    $boxes[] = $box;
                }
            }

            AdminRestaurant::query()->create([
                'slug' => $r['id'],
                'name' => $r['name'],
                'area' => $r['area'] ?? '',
                'city' => $r['city'] ?? '',
                'rating' => $r['rating'] ?? 0,
                'reviews_count' => 50,
                'description' => $r['subtitle'] ?? '',
                'image_url' => $r['image'] ?? '',
                'status' => ($r['boxes_available'] ?? 0) > 0 ? 'active' : 'pending',
                'sort_order' => $sort++,
                'boxes_json' => $boxes,
            ]);
        }
    }
}
