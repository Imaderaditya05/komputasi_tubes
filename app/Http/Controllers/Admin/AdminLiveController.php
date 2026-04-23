<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SurpriseBiteController;
use App\Models\AdminRestaurant;
use App\Models\CheckoutOrder;
use App\Models\Setting;
use App\Models\User;
use App\Services\TransactionMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminLiveController extends Controller
{
    private function fmtIdr(int $n): string
    {
        return 'Rp '.number_format($n, 0, ',', '.');
    }

    private function moneyShort(int $idr): string
    {
        if ($idr >= 1_000_000) {
            return 'Rp '.number_format($idr / 1_000_000, 1, ',', '.').'M';
        }
        if ($idr >= 1_000) {
            return 'Rp '.round($idr / 1_000).'K';
        }

        return 'Rp '.number_format($idr, 0, ',', '.');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $totalCustomers = (int) User::where('role', 'customer')->count();
        $totalTransactions = CheckoutOrder::count();
        $todayStart = now()->startOfDay();
        $ordersToday = CheckoutOrder::where('created_at', '>=', $todayStart)->count();
        $revenueToday = (int) CheckoutOrder::where('created_at', '>=', $todayStart)->sum('amount_idr');

        $recentOrders = CheckoutOrder::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $paymentLabel = static function (string $method): string {
            return match ($method) {
                'va' => 'Midtrans VA',
                'cod' => 'Bayar di tempat',
                default => strtoupper($method),
            };
        };

        $money = fn (int $n) => $this->fmtIdr($n);

        $recentHtml = view('surprisebite.admin.partials.recent-orders-feed', [
            'recentOrders' => $recentOrders,
            'money' => $money,
            'paymentLabel' => $paymentLabel,
        ])->render();

        return response()->json([
            'stats' => [
                'total_customers' => $totalCustomers,
                'total_transactions' => $totalTransactions,
                'orders_today' => $ordersToday,
                'revenue_today' => $this->fmtIdr($revenueToday),
            ],
            'recent_orders_html' => $recentHtml,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $statusFilter = $request->query('status');
        $statusFilter = in_array($statusFilter, ['completed', 'pending', 'failed'], true) ? $statusFilter : null;

        $svc = new TransactionMonitoringService;
        $summary = $svc->summary();

        $orders = $svc->paginatedOrders($search, $statusFilter, 15);
        $orders->getCollection()->load(['customer', 'user']);

        $money = fn (int $n) => $this->fmtIdr($n);

        $tbodyHtml = view('surprisebite.admin.partials.transactions-tbody', [
            'orders' => $orders,
            'money' => $money,
        ])->render();

        return response()->json([
            'summary' => [
                'revenue_short' => $this->moneyShort($summary['revenue_idr']),
                'completed' => $summary['completed'],
                'pending' => $summary['pending'],
                'failed' => $summary['failed'],
            ],
            'tbody_html' => $tbodyHtml,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function restaurants(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = AdminRestaurant::query()->orderBy('sort_order')->orderBy('id');

        if ($q !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($w) use ($term): void {
                $w->where('name', 'like', $term)
                    ->orWhere('area', 'like', $term)
                    ->orWhere('city', 'like', $term);
            });
        }

        $list = $query->get();

        $totalRestaurants = AdminRestaurant::count();
        $totalBoxes = (int) AdminRestaurant::query()->get()->sum(function (AdminRestaurant $r) {
            return is_array($r->boxes_json) ? count($r->boxes_json) : 0;
        });
        $activeCount = AdminRestaurant::where('status', 'active')->count();
        $pendingCount = AdminRestaurant::where('status', 'pending')->count();

        $money = fn (int $n) => $this->fmtIdr($n);

        $gridHtml = view('surprisebite.admin.partials.restaurants-cards', [
            'restaurants' => $list,
            'money' => $money,
        ])->render();

        return response()->json([
            'stats' => [
                'total_restaurants' => $totalRestaurants,
                'total_boxes' => $totalBoxes,
                'active' => $activeCount,
                'pending' => $pendingCount,
            ],
            'grid_html' => $gridHtml,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $roleFilter = $request->query('role');
        $roleFilter = in_array($roleFilter, ['customer', 'seller', 'mitra', 'admin'], true) ? $roleFilter : null;

        $base = User::query()->orderByDesc('created_at');

        if ($q !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $base->where(function ($w) use ($term): void {
                $w->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        if ($roleFilter) {
            $base->where('role', $roleFilter);
        }

        $users = $base->paginate(20)->withQueryString();

        $orderCounts = [];
        if (Schema::hasTable('checkout_orders')) {
            foreach ($users as $u) {
                $orderCounts[$u->id] = CheckoutOrder::where('customer_email', $u->email)->count();
            }
        }

        $activeUsers = Schema::hasColumn('users', 'is_active')
            ? User::where(function ($q): void {
                $q->where('is_active', true)->orWhereNull('is_active');
            })->count()
            : User::count();

        $stats = [
            'total' => User::count(),
            'customers' => User::where('role', 'customer')->count(),
            'sellers' => User::whereIn('role', ['seller', 'mitra'])->count(),
            'active' => $activeUsers,
        ];

        $tbodyHtml = view('surprisebite.admin.partials.users-tbody', [
            'users' => $users,
            'orderCounts' => $orderCounts,
        ])->render();

        return response()->json([
            'stats' => $stats,
            'tbody_html' => $tbodyHtml,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function settings(Request $request): JsonResponse
    {
        $defaults = [
            'site_name' => 'SurpriseBite',
            'support_email' => 'support@surprisebite.com',
            'support_phone' => '+62 812-3456-7890',
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'notify_system' => true,
            'notify_email' => true,
            'notify_sms' => false,
            'commission_rate' => 15,
            'delivery_radius_km' => 10,
            'auto_approve_orders' => false,
            'maintenance_mode' => false,
        ];

        $loaded = [];
        foreach ($defaults as $key => $default) {
            $loaded[$key] = Setting::getValue($key, $default);
        }

        return response()->json([
            'settings' => $loaded,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function impact(): JsonResponse
    {
        $metrics = app(SurpriseBiteController::class)->getImpactMetrics();
        $wd = $metrics['wasteDisplay'];
        $wasteLine = number_format((float) $wd['value'], (int) $wd['decimals'], ',', '.').
            ' '.($wd['unit'] === 'ton' ? 'ton' : 'kg');

        $monthlyHtml = view('surprisebite.admin.partials.impact-monthly-trend', [
            'monthlyTrend' => $metrics['monthlyTrend'],
            'trendYear' => $metrics['trendYear'],
        ])->render();

        return response()->json([
            'meals_saved' => number_format($metrics['mealsSaved']),
            'waste_line' => $wasteLine,
            'active_users' => number_format($metrics['activeUsers']),
            'waste_kg_block' => number_format($metrics['wasteKg'], $metrics['wasteKg'] < 10 ? 1 : 0, ',', '.').' kg',
            'waste_tons_line' => number_format($metrics['wasteTons'], 3, ',', '.'),
            'monthly_trend_html' => $monthlyHtml,
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
