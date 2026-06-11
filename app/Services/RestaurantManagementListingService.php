<?php

namespace App\Services;

use App\Models\AdminRestaurant;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class RestaurantManagementListingService
{
    /**
     * @param  string  $filter  all|active|pending|locked|with_boxes
     * @return array<int, array{type: 'admin', admin: AdminRestaurant}|array{type: 'mitra', mitra: Restaurant}>
     */
    public function entries(?string $q, string $filter = 'all'): array
    {
        $q = trim((string) $q);
        $filter = in_array($filter, ['all', 'active', 'pending', 'locked', 'with_boxes'], true) ? $filter : 'all';

        $adminQuery = AdminRestaurant::query()->orderBy('sort_order')->orderBy('id');
        $mitraQuery = Restaurant::query()
            ->with(['user'])
            ->with(['menus' => fn ($rel) => $rel->orderBy('id')])
            ->withCount('menus')
            ->orderBy('name')
            ->orderBy('id');

        if (in_array($filter, ['active', 'pending', 'locked'], true)) {
            $adminQuery->where('status', $filter);
            if (Schema::hasTable('mitra_restaurants') && Schema::hasColumn('mitra_restaurants', 'access_status')) {
                if ($filter === 'active') {
                    $mitraQuery->where(function (Builder $w): void {
                        $w->where('access_status', 'active')->orWhereNull('access_status');
                    });
                } else {
                    $mitraQuery->where('access_status', $filter);
                }
            }
        }

        if ($q !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $adminQuery->where(function (Builder $w) use ($term): void {
                $w->where('name', 'like', $term)
                    ->orWhere('area', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('address_line', 'like', $term)
                    ->orWhere('owner_name', 'like', $term);
            });
            $mitraQuery->where(function (Builder $w) use ($term): void {
                $w->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('address_line', 'like', $term)
                    ->orWhereHas('user', function (Builder $u) use ($term): void {
                        $u->where('name', 'like', $term)->orWhere('email', 'like', $term);
                    });
            });
        }

        $entries = [];
        foreach ($adminQuery->get() as $ar) {
            $entries[] = [
                'type' => 'admin',
                'admin' => $ar,
                'sort_key' => mb_strtolower($ar->name),
            ];
        }
        foreach ($mitraQuery->get() as $mr) {
            $entries[] = [
                'type' => 'mitra',
                'mitra' => $mr,
                'sort_key' => mb_strtolower($mr->name),
            ];
        }

        if ($filter === 'with_boxes') {
            $entries = array_values(array_filter($entries, function (array $row): bool {
                if ($row['type'] === 'admin') {
                    $b = $row['admin']->boxes_json;

                    return is_array($b) && count($b) > 0;
                }
                $m = $row['mitra'];

                return ((int) ($m->menus_count ?? 0)) > 0;
            }));
        }

        usort($entries, fn (array $a, array $b): int => $a['sort_key'] <=> $b['sort_key']);

        return $entries;
    }

    /**
     * @return array{total_restaurants: int, total_boxes: int, active: int, pending: int, locked: int}
     */
    public function stats(): array
    {
        $adminTotal = AdminRestaurant::count();
        $mitraTotal = Restaurant::count();

        $adminBoxes = (int) AdminRestaurant::query()->get()->sum(function (AdminRestaurant $r) {
            return is_array($r->boxes_json) ? count($r->boxes_json) : 0;
        });
        $mitraBoxes = Schema::hasTable('mitra_menus') ? (int) Menu::query()->count() : 0;

        $adminActive = AdminRestaurant::where('status', 'active')->count();
        $adminPending = AdminRestaurant::where('status', 'pending')->count();
        $adminLocked = AdminRestaurant::where('status', 'locked')->count();

        $mitraActive = $this->mitraStatusCount('active');
        $mitraPending = $this->mitraStatusCount('pending');
        $mitraLocked = $this->mitraStatusCount('locked');

        return [
            'total_restaurants' => $adminTotal + $mitraTotal,
            'total_boxes' => $adminBoxes + $mitraBoxes,
            'active' => $adminActive + $mitraActive,
            'pending' => $adminPending + $mitraPending,
            'locked' => $adminLocked + $mitraLocked,
        ];
    }

    private function mitraStatusCount(string $status): int
    {
        if (! Schema::hasTable('mitra_restaurants')) {
            return 0;
        }
        if (! Schema::hasColumn('mitra_restaurants', 'access_status')) {
            return $status === 'active' ? Restaurant::count() : 0;
        }

        return Restaurant::where('access_status', $status)->count();
    }
}
