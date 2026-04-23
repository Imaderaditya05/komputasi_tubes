<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRestaurant;
use App\Models\CheckoutOrder;
use App\Models\Setting;
use App\Models\User;
use App\Services\TransactionMonitoringService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPanelController extends Controller
{
    private function moneyShort(): \Closure
    {
        return function (int $idr): string {
            if ($idr >= 1_000_000) {
                return 'Rp '.number_format($idr / 1_000_000, 1, ',', '.').'M';
            }
            if ($idr >= 1_000) {
                return 'Rp '.round($idr / 1_000).'K';
            }

            return 'Rp '.number_format($idr, 0, ',', '.');
        };
    }

    private function moneyIdr(): \Closure
    {
        return fn (int $n): string => 'Rp '.number_format($n, 0, ',', '.');
    }

    public function transactions(Request $request): View|StreamedResponse
    {
        $search = trim((string) $request->query('q', ''));
        $statusFilter = $request->query('status');
        $statusFilter = in_array($statusFilter, ['completed', 'pending', 'failed'], true) ? $statusFilter : null;

        $svc = new TransactionMonitoringService;
        $summary = $svc->summary();

        if ($request->boolean('export')) {
            $query = $svc->filteredOrdersQuery($search, $statusFilter);
            $orders = $query->with(['customer', 'user'])->get();

            $filename = 'transactions-'.now()->format('Y-m-d-His').'.csv';

            return response()->streamDownload(function () use ($orders): void {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, [
                    'Transaction ID', 'Order ID', 'Customer', 'Restaurant', 'Amount (IDR)',
                    'Payment', 'Status', 'Date',
                ]);
                foreach ($orders as $order) {
                    $bucket = TransactionMonitoringService::displayBucket($order->payment_status);
                    fputcsv($out, [
                        $order->midtrans_transaction_id ?: 'TRX-'.$order->id,
                        $order->public_order_id,
                        $order->user?->name ?? $order->customer?->name ?? $order->customer_email,
                        $order->restaurant_name,
                        $order->amount_idr,
                        $order->payment_method,
                        $bucket,
                        $order->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                    ]);
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $orders = $svc->paginatedOrders($search, $statusFilter, 15);
        $orders->getCollection()->load(['customer', 'user']);

        return view('surprisebite.admin.transactions', [
            'orders' => $orders,
            'summary' => $summary,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'moneyShort' => $this->moneyShort(),
            'money' => $this->moneyIdr(),
        ]);
    }

    public function restaurants(Request $request): View
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

        return view('surprisebite.admin.restaurants', [
            'restaurants' => $list,
            'q' => $q,
            'stats' => [
                'total_restaurants' => $totalRestaurants,
                'total_boxes' => $totalBoxes,
                'active' => $activeCount,
                'pending' => $pendingCount,
            ],
            'money' => $this->moneyIdr(),
        ]);
    }

    public function storeRestaurant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:120'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string', 'max:2000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'box_title' => ['nullable', 'string', 'max:200'],
            'box_price' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,pending'],
        ]);

        $slugBase = Str::slug($validated['name']);
        $slug = $slugBase;
        $i = 1;
        while (AdminRestaurant::where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$i++;
        }

        $boxes = [];
        if (! empty($validated['box_title']) && isset($validated['box_price']) && $validated['box_price'] > 0) {
            $boxSlug = Str::slug($validated['box_title']);
            if ($boxSlug === '') {
                $boxSlug = 'mystery-box-'.$slug;
            }
            $boxes[] = [
                'slug' => $boxSlug,
                'title' => $validated['box_title'],
                'restaurant_id' => $slug,
                'category' => 'restaurant',
                'category_label' => 'Restaurant',
                'filter_key' => 'restaurant',
                'card_rating' => (float) ($validated['rating'] ?? 4.5),
                'image' => $validated['image_url'] ?? '',
                'price' => (int) $validated['box_price'],
                'original_price' => (int) round($validated['box_price'] * 3),
                'pickup_time' => '20:00 - 21:00',
                'badge' => 'New',
                'distance_km' => 2,
                'stock' => 5,
                'description' => $validated['description'] ?? 'Mystery box dari restoran ini.',
                'highlights' => ['Surprise', 'Hemat', 'Kurangi food waste'],
            ];
        }

        AdminRestaurant::create([
            'slug' => $slug,
            'name' => $validated['name'],
            'area' => $validated['location'] ?? '',
            'city' => '',
            'rating' => $validated['rating'] ?? 0,
            'reviews_count' => $validated['reviews'] ?? 0,
            'description' => $validated['description'] ?? '',
            'image_url' => $validated['image_url'] ?? '',
            'status' => $validated['status'] ?? 'active',
            'sort_order' => (int) (AdminRestaurant::max('sort_order') ?? 0) + 1,
            'boxes_json' => $boxes,
        ]);

        return redirect()->route('admin.restaurants')->with('status', 'Restoran berhasil ditambahkan.');
    }

    public function updateRestaurant(Request $request, AdminRestaurant $adminRestaurant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:120'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string', 'max:2000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'box_title' => ['nullable', 'string', 'max:200'],
            'box_price' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,pending'],
        ]);

        $boxes = is_array($adminRestaurant->boxes_json) ? $adminRestaurant->boxes_json : [];
        if (! empty($validated['box_title']) && isset($validated['box_price']) && $validated['box_price'] > 0) {
            $first = $boxes[0] ?? [];
            $boxes = [[
                ...$first,
                'slug' => $first['slug'] ?? Str::slug($validated['box_title']).'-'.$adminRestaurant->slug,
                'title' => $validated['box_title'],
                'restaurant_id' => $adminRestaurant->slug,
                'price' => (int) $validated['box_price'],
                'original_price' => (int) ($first['original_price'] ?? round($validated['box_price'] * 3)),
                'image' => $validated['image_url'] ?? ($first['image'] ?? ''),
                'card_rating' => (float) ($validated['rating'] ?? ($first['card_rating'] ?? 4.5)),
                'description' => $validated['description'] ?? ($first['description'] ?? ''),
            ]];
        }

        $adminRestaurant->update([
            'name' => $validated['name'],
            'area' => $validated['location'] ?? $adminRestaurant->area,
            'rating' => $validated['rating'] ?? $adminRestaurant->rating,
            'reviews_count' => $validated['reviews'] ?? $adminRestaurant->reviews_count,
            'description' => $validated['description'] ?? '',
            'image_url' => $validated['image_url'] ?? '',
            'status' => $validated['status'] ?? $adminRestaurant->status,
            'boxes_json' => $boxes,
        ]);

        return redirect()->route('admin.restaurants')->with('status', 'Restoran diperbarui.');
    }

    public function destroyRestaurant(AdminRestaurant $adminRestaurant): RedirectResponse
    {
        $adminRestaurant->delete();

        return redirect()->route('admin.restaurants')->with('status', 'Restoran dihapus.');
    }

    public function users(Request $request): View
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

        return view('surprisebite.admin.users', [
            'users' => $users,
            'q' => $q,
            'roleFilter' => $roleFilter,
            'orderCounts' => $orderCounts,
            'stats' => $stats,
        ]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $isSelf = $actor && $actor->id === $user->id;

        if ($user->role === 'admin' && ! $isSelf) {
            return redirect()->route('admin.users')->with('status', 'Tidak dapat mengedit akun admin lain.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:32'],
        ];

        if ($user->role !== 'admin') {
            $rules['role'] = ['required', 'in:customer,seller,mitra'];
        }

        $validated = $request->validate($rules);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if (isset($validated['role']) && $user->role !== 'admin') {
            $user->role = $validated['role'];
        }

        $user->save();

        return redirect()->route('admin.users')->with('status', 'Pengguna diperbarui.');
    }

    public function toggleUserActive(Request $request, User $user): RedirectResponse
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.users')->with('status', 'Akun admin tidak dapat dinonaktifkan dari sini.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return redirect()->route('admin.users')->with('status', 'Status pengguna diperbarui.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()?->id) {
            return redirect()->route('admin.users')->with('status', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.users')->with('status', 'Tidak dapat menghapus akun admin.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('status', 'Pengguna dihapus.');
    }

    private function defaultSettings(): array
    {
        return [
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
    }

    public function settings(): View
    {
        $defaults = $this->defaultSettings();
        $loaded = [];
        foreach ($defaults as $key => $default) {
            $loaded[$key] = Setting::getValue($key, $default);
            if (! is_bool($loaded[$key]) && in_array($key, ['notify_system', 'notify_email', 'notify_sms', 'auto_approve_orders', 'maintenance_mode'], true)) {
                $loaded[$key] = (bool) $loaded[$key];
            }
        }

        return view('surprisebite.admin.settings', ['settings' => $loaded]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:200'],
            'support_email' => ['required', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:64'],
            'language' => ['nullable', 'string', 'max:16'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'notify_system' => ['sometimes', 'boolean'],
            'notify_email' => ['sometimes', 'boolean'],
            'notify_sms' => ['sometimes', 'boolean'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'delivery_radius_km' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'auto_approve_orders' => ['sometimes', 'boolean'],
            'maintenance_mode' => ['sometimes', 'boolean'],
        ]);

        $bool = fn (string $k): bool => $request->boolean($k);

        $map = [
            'site_name' => $validated['site_name'],
            'support_email' => $validated['support_email'],
            'support_phone' => $validated['support_phone'] ?? '',
            'language' => $validated['language'] ?? 'id',
            'timezone' => $validated['timezone'] ?? config('app.timezone'),
            'notify_system' => $bool('notify_system'),
            'notify_email' => $bool('notify_email'),
            'notify_sms' => $bool('notify_sms'),
            'commission_rate' => (float) ($validated['commission_rate'] ?? 15),
            'delivery_radius_km' => (float) ($validated['delivery_radius_km'] ?? 10),
            'auto_approve_orders' => $bool('auto_approve_orders'),
            'maintenance_mode' => $bool('maintenance_mode'),
        ];

        foreach ($map as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->route('admin.settings')->with('status', 'Pengaturan disimpan.');
    }
}
