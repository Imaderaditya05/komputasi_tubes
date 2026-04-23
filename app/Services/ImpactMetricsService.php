<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ImpactMetricsService
{
    /**
     * Status pembayaran yang dianggap mystery box “terselamatkan” (sesuai alur Midtrans di app).
     * PAID = settlement/capture; PENDING_COD = COD menunggu bayar ke kurir.
     */
    public const IMPACT_STATUSES = ['PAID', 'PENDING_COD'];

    /**
     * Estimated kg of retail food waste avoided per mystery box (1 box ≈ 1 meal diverted).
     * Used for “food waste reduced” everywhere it’s shown.
     */
    private const WASTE_KG_PER_BOX = 2.2;

    public function impactOrdersQuery(): Builder
    {
        return CheckoutOrder::query()
            ->whereIn('payment_status', self::IMPACT_STATUSES);
    }

    public function totalMealsSaved(): int
    {
        return (int) $this->impactOrdersQuery()->count();
    }

    public function foodWasteReducedKg(): float
    {
        return $this->totalMealsSaved() * self::WASTE_KG_PER_BOX;
    }

    public function foodWasteReducedTons(): float
    {
        return $this->foodWasteReducedKg() / 1000;
    }

    /** @return array{value: float, unit: 'kg'|'ton', decimals: int, label_id: string} */
    public function foodWasteDisplay(): array
    {
        $kg = $this->foodWasteReducedKg();
        if ($kg < 500) {
            $decimals = $kg > 0 && $kg < 10 ? 1 : 0;

            return [
                'value' => round($kg, $decimals),
                'unit' => 'kg',
                'decimals' => $decimals,
                'label_id' => 'limbah makanan dicegah',
            ];
        }

        $ton = $kg / 1000;
        $tDec = $ton >= 10 ? 1 : 2;

        return [
            'value' => round($ton, $tDec),
            'unit' => 'ton',
            'decimals' => $tDec,
            'label_id' => 'limbah makanan dicegah',
        ];
    }

    /** Jumlah email pelanggan unik yang punya minimal satu pesanan qualifying. */
    public function activeRescueUsersCount(): int
    {
        return (int) DB::table('checkout_orders')
            ->whereIn('payment_status', self::IMPACT_STATUSES)
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->selectRaw('COUNT(DISTINCT customer_email) as c')
            ->value('c');
    }

    /**
     * Monthly breakdown (YTD for current year): meals + waste (tons).
     *
     * @return list<array{m: string, meals: int, waste_tons: float, waste_kg: float}>
     */
    public function monthlyTrendForYear(int $year): array
    {
        $rows = $this->impactOrdersQuery()
            ->select([
                DB::raw('MONTH(created_at) as m'),
                DB::raw('COUNT(*) as meals'),
            ])
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get()
            ->keyBy(fn ($row) => (int) $row->m);

        $labels = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $out = [];
        $lastMonth = $year === (int) now()->year ? (int) now()->month : 12;
        for ($month = 1; $month <= $lastMonth; $month++) {
            $meals = (int) ($rows->get($month)?->meals ?? 0);
            $wasteKg = $meals * self::WASTE_KG_PER_BOX;
            $out[] = [
                'm' => $labels[$month],
                'meals' => $meals,
                'waste_kg' => round($wasteKg, 1),
                'waste_tons' => round($wasteKg / 1000, 3),
            ];
        }

        return $out;
    }

    public function trendYearLabel(?int $year = null): string
    {
        $y = $year ?? (int) now()->year;

        return (string) $y;
    }
}
