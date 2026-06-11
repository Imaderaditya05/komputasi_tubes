<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SurpriseBiteController;
use App\Models\CheckoutOrder;
use App\Models\Setting;
use App\Models\User;
use App\Services\AdminUserListService;
use App\Services\RestaurantManagementListingService;
use App\Services\TransactionMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $filter = (string) $request->query('filter', 'all');
        $filter = in_array($filter, ['all', 'active', 'pending', 'locked', 'with_boxes'], true) ? $filter : 'all';
        $listingSvc = new RestaurantManagementListingService;
        $entries = $listingSvc->entries($q, $filter);
        $stats = $listingSvc->stats();

        $money = fn (int $n) => $this->fmtIdr($n);

        $gridHtml = view('surprisebite.admin.partials.restaurants-cards', [
            'entries' => $entries,
            'money' => $money,
        ])->render();

        return response()->json([
            'stats' => [
                'total_restaurants' => $stats['total_restaurants'],
                'total_boxes' => $stats['total_boxes'],
                'active' => $stats['active'],
                'pending' => $stats['pending'],
                'locked' => $stats['locked'],
            ],
            'grid_html' => $gridHtml,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $listSvc = new AdminUserListService;
        $data = $listSvc->paginatedForAdmin($request);
        $users = $data['users'];
        $orderCounts = $data['orderCounts'];
        $stats = $listSvc->stats();

        $tbodyHtml = view('surprisebite.admin.partials.users-tbody', [
            'users' => $users,
            'orderCounts' => $orderCounts,
            'authUserId' => auth()->id(),
        ])->render();

        $paginationHtml = view('surprisebite.admin.partials.users-pagination-fragment', [
            'users' => $users,
        ])->render();

        return response()->json([
            'stats' => $stats,
            'tbody_html' => $tbodyHtml,
            'pagination_html' => $paginationHtml,
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
