<?php

namespace App\Services;

use App\Models\AdminRestaurant;
use App\Models\CheckoutOrder;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Schema;

class OrderMapLocationService
{
    /**
     * @return array{
     *   restaurantLat: ?float,
     *   restaurantLng: ?float,
     *   mapQuery: string,
     *   mapQueryAlternates: list<string>
     * }
     */
    public function resolveForCheckoutOrder(CheckoutOrder $order): array
    {
        $catalog = app(CatalogRepository::class);
        $rMeta = $catalog->getRestaurantForBoxSlug($order->box_slug);
        $mitraLoc = $this->resolveMitraLocation($order);
        $adminLoc = ($mitraLoc === null && ! is_array($rMeta))
            ? $this->adminRestaurantLocationByName($order->restaurant_name)
            : null;

        if ($mitraLoc !== null) {
            $coords = $this->pickRestaurantCoordinates($mitraLoc, $order, is_array($rMeta) ? $rMeta : null);
            $restaurantLat = $coords['lat'];
            $restaurantLng = $coords['lng'];
            $mapQuery = $mitraLoc['map_query'];
        } elseif (is_array($rMeta)) {
            $coords = $this->pickRestaurantCoordinates(null, $order, $rMeta);
            $restaurantLat = $coords['lat'];
            $restaurantLng = $coords['lng'];
            $mapQuery = ! empty($rMeta['map_query'])
                ? (string) $rMeta['map_query']
                : trim($order->restaurant_name.', Indonesia');
        } elseif ($adminLoc !== null) {
            $coords = $this->pickRestaurantCoordinates($adminLoc, $order, null);
            $restaurantLat = $coords['lat'];
            $restaurantLng = $coords['lng'];
            $mapQuery = $adminLoc['map_query'];
        } else {
            $coords = $this->pickRestaurantCoordinates(null, $order, null);
            $restaurantLat = $coords['lat'];
            $restaurantLng = $coords['lng'];
            $mapQuery = trim($order->restaurant_name.', Indonesia');
        }

        return [
            'restaurantLat' => $restaurantLat,
            'restaurantLng' => $restaurantLng,
            'mapQuery' => $mapQuery,
            'mapQueryAlternates' => $this->buildMapQueryAlternates($order, $mitraLoc, $adminLoc),
        ];
    }

    /**
     * @return array{latitude: ?float, longitude: ?float, map_query: string, address_line: ?string}|null
     */
    public function resolveMitraLocation(CheckoutOrder $order): ?array
    {
        $fromMenu = $this->mitraLocationFromMenuSlug($order->box_slug);
        if ($fromMenu !== null) {
            return $fromMenu;
        }

        if (! str_starts_with($order->box_slug, 'mitra-menu-')) {
            return null;
        }

        return $this->mitraLocationByRestaurantName($order->restaurant_name);
    }

    /**
     * @return array{latitude: ?float, longitude: ?float, map_query: string, address_line: ?string}|null
     */
    private function mitraLocationFromMenuSlug(string $boxSlug): ?array
    {
        if (! str_starts_with($boxSlug, 'mitra-menu-')) {
            return null;
        }

        $menuId = (int) substr($boxSlug, strlen('mitra-menu-'));
        if ($menuId < 1) {
            return null;
        }

        $menu = Menu::query()->with('restaurant')->find($menuId);
        $restaurant = $menu?->restaurant;

        return $restaurant instanceof Restaurant ? $this->restaurantModelToLocationArray($restaurant) : null;
    }

    /**
     * @return array{latitude: ?float, longitude: ?float, map_query: string, address_line: ?string}|null
     */
    private function mitraLocationByRestaurantName(?string $name): ?array
    {
        $name = trim((string) $name);
        if ($name === '' || ! Schema::hasTable('mitra_restaurants')) {
            return null;
        }

        $r = Restaurant::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->first();

        if ($r === null) {
            $r = Restaurant::query()
                ->where('name', 'like', '%'.$name.'%')
                ->orderByDesc('updated_at')
                ->first();
        }

        return $r instanceof Restaurant ? $this->restaurantModelToLocationArray($r) : null;
    }

    /**
     * @return array{latitude: ?float, longitude: ?float, map_query: string, address_line: ?string}|null
     */
    private function adminRestaurantLocationByName(?string $name): ?array
    {
        $name = trim((string) $name);
        if ($name === '' || ! Schema::hasTable('admin_restaurants')) {
            return null;
        }

        $r = AdminRestaurant::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->first();

        if ($r === null) {
            $r = AdminRestaurant::query()
                ->where('name', 'like', '%'.$name.'%')
                ->orderByDesc('updated_at')
                ->first();
        }

        if ($r === null) {
            return null;
        }

        $mapParts = array_filter([
            $r->name,
            $r->area,
            $r->city,
            $r->address_line,
            'Indonesia',
        ], fn ($v) => filled($v));

        $mapQuery = count($mapParts) ? implode(', ', $mapParts) : $r->name.', Indonesia';

        return [
            'latitude' => $this->normalizeCoordinate($r->latitude),
            'longitude' => $this->normalizeCoordinate($r->longitude),
            'map_query' => $mapQuery,
            'address_line' => $r->address_line,
        ];
    }

    /**
     * @param  array{latitude: ?float, longitude: ?float, map_query: string, address_line: ?string}|null  $loc
     * @param  array<string, mixed>|null  $rMeta
     * @return array{lat: ?float, lng: ?float}
     */
    private function pickRestaurantCoordinates(?array $loc, CheckoutOrder $order, ?array $rMeta): array
    {
        $pairs = [
            [$loc['latitude'] ?? null, $loc['longitude'] ?? null],
            [$order->restaurant_latitude, $order->restaurant_longitude],
            [is_array($rMeta) ? ($rMeta['latitude'] ?? null) : null, is_array($rMeta) ? ($rMeta['longitude'] ?? null) : null],
        ];

        foreach ($pairs as [$lat, $lng]) {
            $la = $this->normalizeCoordinate($lat);
            $ln = $this->normalizeCoordinate($lng);
            if ($this->coordinatesPlausibleIndonesia($la, $ln)) {
                return ['lat' => $la, 'lng' => $ln];
            }
        }

        return ['lat' => null, 'lng' => null];
    }

    /**
     * @param  array{latitude: ?float, longitude: ?float, map_query: string, address_line: ?string}|null  $mitraLoc
     * @param  array{latitude: ?float, longitude: ?float, map_query: string, address_line: ?string}|null  $adminLoc
     * @return list<string>
     */
    private function buildMapQueryAlternates(CheckoutOrder $order, ?array $mitraLoc, ?array $adminLoc): array
    {
        $rows = [
            trim($order->restaurant_name.', Indonesia'),
            filled($mitraLoc['address_line'] ?? null) ? trim((string) $mitraLoc['address_line'].', Indonesia') : null,
            filled($mitraLoc['address_line'] ?? null) ? trim((string) $mitraLoc['address_line']) : null,
            filled($adminLoc['address_line'] ?? null) ? trim((string) $adminLoc['address_line'].', Indonesia') : null,
            filled($adminLoc['address_line'] ?? null) ? trim((string) $adminLoc['address_line']) : null,
        ];

        return array_values(array_unique(array_filter($rows, fn ($v) => filled($v))));
    }

    /**
     * @return array{latitude: ?float, longitude: ?float, map_query: string, address_line: ?string}
     */
    private function restaurantModelToLocationArray(Restaurant $restaurant): array
    {
        $mapParts = array_filter([
            $restaurant->name,
            $restaurant->address_line,
            'Indonesia',
        ], fn ($v) => filled($v));

        $mapQuery = count($mapParts) ? implode(', ', $mapParts) : $restaurant->name.', Indonesia';

        return [
            'latitude' => $this->normalizeCoordinate($restaurant->latitude),
            'longitude' => $this->normalizeCoordinate($restaurant->longitude),
            'map_query' => $mapQuery,
            'address_line' => $restaurant->address_line,
        ];
    }

    public function normalizeCoordinate(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $f = (float) $value;
        if (abs($f) < 1e-7) {
            return null;
        }

        return $f;
    }

    public function coordinatesPlausibleIndonesia(?float $lat, ?float $lng): bool
    {
        if ($lat === null || $lng === null) {
            return false;
        }

        return $lat >= -11.5 && $lat <= 6.5 && $lng >= 94.0 && $lng <= 141.5;
    }
}
