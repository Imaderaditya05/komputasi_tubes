<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use App\Models\CustomerPartnerReport;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class MitraRestaurantFeedbackService
{
    /**
     * @return list<string>
     */
    public function menuSlugsForRestaurant(Restaurant $restaurant): array
    {
        return $restaurant->menus()->pluck('id')->map(fn (int|string $id) => 'mitra-menu-'.$id)->all();
    }

    /**
     * @return Builder<int, CheckoutOrder>
     */
    public function checkoutOrdersQuery(Restaurant $restaurant): Builder
    {
        $slugs = $this->menuSlugsForRestaurant($restaurant);
        $base = CheckoutOrder::query()->orderByDesc('created_at');
        if ($slugs === []) {
            return $base->whereRaw('1 = 0');
        }

        return $base->whereIn('box_slug', $slugs);
    }

    public function checkoutOrdersCount(Restaurant $restaurant): int
    {
        return (int) $this->checkoutOrdersQuery($restaurant)->count();
    }

    /**
     * Ulasan pesanan untuk menu-menu restoran ini (slug mitra-menu-{id}).
     *
     * @return Collection<int, CheckoutOrder>
     */
    public function recentOrderReviews(Restaurant $restaurant, int $limit = 40): Collection
    {
        $slugs = $this->menuSlugsForRestaurant($restaurant);
        if ($slugs === []) {
            return collect();
        }

        return CheckoutOrder::query()
            ->whereIn('box_slug', $slugs)
            ->where(function ($q): void {
                $q->where(function ($q2): void {
                    $q2->whereNotNull('customer_rating')
                        ->where('customer_rating', '>=', 1)
                        ->where('customer_rating', '<=', 5);
                })->orWhere(function ($q2): void {
                    $q2->whereNotNull('customer_review_comment')
                        ->where('customer_review_comment', '!=', '');
                });
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get([
                'public_order_id',
                'box_slug',
                'box_title',
                'customer_rating',
                'customer_review_comment',
                'customer_email',
                'updated_at',
                'fulfillment_status',
            ]);
    }

    /**
     * Prefer agregasi mitra_menus (sinkron dengan MitraMenuRatingAggregator).
     *
     * @return array{avg: ?float, count: int}
     */
    public function aggregateRatingSummary(Restaurant $restaurant): array
    {
        $fromMenus = $this->weightedAverageFromMenus($restaurant);
        if ($fromMenus['count'] > 0) {
            return $fromMenus;
        }

        return $this->weightedAverageFromOrders($restaurant);
    }

    /**
     * @return array{avg: ?float, count: int}
     */
    private function weightedAverageFromMenus(Restaurant $restaurant): array
    {
        $menus = $restaurant->menus()->where('ratings_count', '>', 0)->get(['avg_rating', 'ratings_count']);
        if ($menus->isEmpty()) {
            return ['avg' => null, 'count' => 0];
        }

        $totalWeighted = 0.0;
        $totalCount = 0;
        foreach ($menus as $menu) {
            $c = (int) ($menu->ratings_count ?? 0);
            if ($c < 1) {
                continue;
            }
            $totalWeighted += (float) ($menu->avg_rating ?? 0) * $c;
            $totalCount += $c;
        }

        if ($totalCount === 0) {
            return ['avg' => null, 'count' => 0];
        }

        return ['avg' => round($totalWeighted / $totalCount, 2), 'count' => $totalCount];
    }

    /**
     * Fallback jika mitra_menus belum berisi ratings_count tetapi checkout_orders ada skor.
     *
     * @return array{avg: ?float, count: int}
     */
    private function weightedAverageFromOrders(Restaurant $restaurant): array
    {
        $slugs = $this->menuSlugsForRestaurant($restaurant);
        if ($slugs === []) {
            return ['avg' => null, 'count' => 0];
        }

        $base = CheckoutOrder::query()
            ->whereIn('box_slug', $slugs)
            ->whereNotNull('customer_rating')
            ->whereBetween('customer_rating', [1, 5]);

        $count = (int) (clone $base)->count();
        if ($count === 0) {
            return ['avg' => null, 'count' => 0];
        }

        /** @var float|string|null $avgRaw */
        $avgRaw = (clone $base)->avg('customer_rating');

        return [
            'avg' => round((float) $avgRaw, 2),
            'count' => $count,
        ];
    }

    public function pendingReportsCount(Restaurant $restaurant): int
    {
        if (! Schema::hasTable('customer_partner_reports')) {
            return 0;
        }

        return (int) CustomerPartnerReport::query()
            ->where('mitra_restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->count();
    }

    /**
     * @return Collection<int, CustomerPartnerReport>
     */
    public function reports(Restaurant $restaurant, int $limit = 50): Collection
    {
        if (! Schema::hasTable('customer_partner_reports')) {
            return collect();
        }

        return CustomerPartnerReport::query()
            ->where('mitra_restaurant_id', $restaurant->id)
            ->with(['reporter:id,name,email'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /** Singkatkan email untuk ditampilkan ke mitra. */
    public static function maskEmail(?string $email): string
    {
        $email = $email !== null ? trim($email) : '';
        if ($email === '' || ! str_contains($email, '@')) {
            return '—';
        }

        [$local, $domain] = explode('@', $email, 2);
        $local = (string) $local;
        if ($local === '') {
            return '—';
        }

        $first = mb_substr($local, 0, 1);

        return $first.'***@'.$domain;
    }
}
