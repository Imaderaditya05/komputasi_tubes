<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MitraSalesAnalyticsService
{
    /** Pembayaran yang dihitung sebagai penjualan tercatat (selaras ImpactMetricsService). */
    public function countedSoldPaymentStatuses(): array
    {
        return ['PAID', 'PENDING_COD'];
    }

    /**
     * @return Builder<CheckoutOrder>
     */
    private function checkoutBase(Restaurant $restaurant): Builder
    {
        return app(MitraRestaurantFeedbackService::class)->checkoutOrdersQuery($restaurant);
    }

    /** Sidik tambahan untuk polling omzet/order «tercatat». */
    public function paidOrdersFingerprint(Restaurant $restaurant): string
    {
        $row = $this->checkoutBase($restaurant)
            ->clone()
            ->reorder()
            ->whereIn('payment_status', $this->countedSoldPaymentStatuses())
            ->selectRaw('COALESCE(SUM(amount_idr), 0) AS g, COUNT(*) AS c, MAX(updated_at) AS mx')
            ->first();

        if ($row === null) {
            return '0|0|';
        }

        $mx = $row->mx !== null ? (string) $row->mx : '';

        return ((int) ($row->g ?? 0)).'|'.((int) ($row->c ?? 0)).'|'.$mx;
    }

    /**
     * @return array{start: Carbon|null, end: Carbon|null, label: string}
     */
    public function resolvePeriod(?string $key): array
    {
        $k = strtolower((string) $key);
        $now = now();

        return match ($k) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => 'Hari ini',
            ],
            '7d' => [
                'start' => $now->copy()->subDays(7)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => '7 hari terakhir',
            ],
            '30d' => [
                'start' => $now->copy()->subDays(30)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => '30 hari terakhir',
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
                'label' => 'Bulan ini',
            ],
            'all' => [
                'start' => null,
                'end' => null,
                'label' => 'Sepanjang waktu',
            ],
            default => [
                'start' => $now->copy()->subDays(30)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => '30 hari terakhir',
            ],
        };
    }

    public function sanitizePeriod(?string $key): string
    {
        $k = strtolower(trim((string) $key));

        return in_array($k, ['today', '7d', '30d', 'month', 'all'], true) ? $k : '30d';
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPayload(Restaurant $restaurant, string $periodKey): array
    {
        $periodKey = $this->sanitizePeriod($periodKey);
        $bounds = $this->resolvePeriod($periodKey);

        $soldBase = $this->checkoutBase($restaurant)->clone()->whereIn(
            'payment_status',
            $this->countedSoldPaymentStatuses(),
        );
        $this->applyCreatedBetween($soldBase, $bounds['start'], $bounds['end']);

        $agg = $soldBase->clone()
            ->reorder()
            ->selectRaw('COUNT(*) AS order_count_agg')
            ->selectRaw('COALESCE(SUM(amount_idr), 0) AS gross_idr_agg')
            ->selectRaw('COALESCE(SUM(COALESCE(item_quantity, 1)), 0) AS units_agg')
            ->first();

        $orderCount = $agg !== null ? (int) ($agg->order_count_agg ?? 0) : 0;
        $grossIdr = $agg !== null ? (int) ($agg->gross_idr_agg ?? 0) : 0;
        $unitsSold = $agg !== null ? (int) ($agg->units_agg ?? 0) : 0;

        $avgOrderIdr = $orderCount > 0 ? (int) round($grossIdr / $orderCount) : 0;

        $completedCount = (int) $soldBase->clone()->where('fulfillment_status', 'completed')->count();

        $pendingGateway = $this->checkoutBase($restaurant)->clone()
            ->where('payment_status', 'PENDING');
        $this->applyCreatedBetween($pendingGateway, $bounds['start'], $bounds['end']);
        $pendingGatewayRow = $pendingGateway->clone()
            ->reorder()
            ->selectRaw('COUNT(*) AS oc')
            ->selectRaw('COALESCE(SUM(amount_idr), 0) AS g')
            ->first();
        $pendingGatewayCount = $pendingGatewayRow !== null ? (int) ($pendingGatewayRow->oc ?? 0) : 0;
        $pendingGatewayIdr = $pendingGatewayRow !== null ? (int) ($pendingGatewayRow->g ?? 0) : 0;

        $menusById = $restaurant->menus()->pluck('name', 'id')->all();

        $slugRows = $soldBase->clone()
            ->reorder()
            ->selectRaw('box_slug AS slug')
            ->selectRaw('COUNT(*) AS order_count_agg')
            ->selectRaw('COALESCE(SUM(COALESCE(item_quantity, 1)), 0) AS units_agg')
            ->selectRaw('COALESCE(SUM(amount_idr), 0) AS gross_agg')
            ->groupBy('box_slug')
            ->orderByDesc('gross_agg')
            ->limit(50)
            ->get();

        $byMenu = $slugRows->map(function ($row) use ($menusById): array {
            $slug = (string) ($row->slug ?? '');
            $mid = preg_match('/^mitra-menu-(\d+)$/', $slug, $m) ? (int) $m[1] : 0;

            /** @phpstan-ignore-next-line */
            $units = isset($row->units_agg) ? (int) $row->units_agg : 0;
            /** @phpstan-ignore-next-line */
            $grossAgg = isset($row->gross_agg) ? (int) $row->gross_agg : 0;
            /** @phpstan-ignore-next-line */
            $occ = isset($row->order_count_agg) ? (int) $row->order_count_agg : 0;

            return [
                'menu_id' => $mid > 0 ? $mid : null,
                'name' => $mid > 0 && isset($menusById[$mid]) ? $menusById[$mid] : ($slug ?: '—'),
                'slug' => $slug,
                'orders' => $occ,
                'units' => $units,
                'gross_idr' => $grossAgg,
            ];
        })->values()->all();

        return [
            'period' => $periodKey,
            'period_label' => $bounds['label'],
            'gross_idr' => $grossIdr,
            'order_count' => $orderCount,
            'units_sold' => $unitsSold,
            'avg_order_idr' => $avgOrderIdr,
            'completed_orders' => $completedCount,
            'pending_gateway_count' => $pendingGatewayCount,
            'pending_gateway_idr' => $pendingGatewayIdr,
            'by_menu' => $byMenu,
        ];
    }

    private function applyCreatedBetween(
        Builder $q,
        ?Carbon $start,
        ?Carbon $end,
    ): void {
        if ($start instanceof Carbon) {
            $q->where('created_at', '>=', $start);
        }
        if ($end instanceof Carbon) {
            $q->where('created_at', '<=', $end);
        }
    }
}
