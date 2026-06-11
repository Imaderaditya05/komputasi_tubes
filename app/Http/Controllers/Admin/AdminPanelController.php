<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRestaurant;
use App\Models\CheckoutOrder;
use App\Models\CustomerPartnerReport;
use App\Models\MitraAccessAppeal;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Services\AdminUserListService;
use App\Services\RestaurantManagementListingService;
use App\Services\TransactionMonitoringService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
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
                        $order->customer?->name ?? $order->user?->name ?? ($order->customer_email ? explode('@', $order->customer_email)[0] : '—'),
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
        $listingSvc = new RestaurantManagementListingService;
        $filter = (string) $request->query('filter', 'all');
        $filter = in_array($filter, ['all', 'active', 'pending', 'locked', 'with_boxes'], true) ? $filter : 'all';
        $entries = $listingSvc->entries($q, $filter);
        $stats = $listingSvc->stats();

        $pendingAccessAppeals = MitraAccessAppeal::query()
            ->where('status', 'pending')
            ->with(['restaurant', 'user'])
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $pendingCustomerReports = CustomerPartnerReport::query()
            ->pending()
            ->with(['reporter'])
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        // Fetch pending mitra users who need approval
        $pendingMitraUsers = User::query()
            ->where('role', 'mitra')
            ->where('mitra_approval_status', 'pending')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('surprisebite.admin.restaurants', [
            'entries' => $entries,
            'q' => $q,
            'listingFilter' => $filter,
            'stats' => $stats,
            'money' => $this->moneyIdr(),
            'pendingAccessAppeals' => $pendingAccessAppeals,
            'pendingCustomerReports' => $pendingCustomerReports,
            'pendingMitraUsers' => $pendingMitraUsers,
        ]);
    }

    public function updateMitraAccess(Request $request, AdminRestaurant $adminRestaurant): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,pending,locked'],
        ]);

        $adminRestaurant->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Status akses mitra berhasil disimpan.',
            'restaurant' => [
                'id' => $adminRestaurant->id,
                'status' => $adminRestaurant->status,
            ],
        ]);
    }

    public function updateMitraRestaurantAccess(Request $request, Restaurant $mitraRestaurant): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,pending,locked'],
        ]);

        $mitraRestaurant->update([
            'access_status' => $validated['status'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Status akses mitra berhasil disimpan.',
            'restaurant' => [
                'id' => $mitraRestaurant->id,
                'status' => $mitraRestaurant->access_status,
            ],
        ]);
    }

    public function storeRestaurant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'owner_name' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:120'],
            'address_line' => ['nullable', 'string', 'max:500'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string', 'max:2000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'box_title' => ['nullable', 'string', 'max:200'],
            'box_price' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,pending,locked'],
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
            'owner_name' => $validated['owner_name'] ?? null,
            'area' => $validated['location'] ?? '',
            'address_line' => $validated['address_line'] ?? null,
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
            'owner_name' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:120'],
            'address_line' => ['nullable', 'string', 'max:500'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string', 'max:2000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'box_title' => ['nullable', 'string', 'max:200'],
            'box_price' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,pending,locked'],
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
            'owner_name' => array_key_exists('owner_name', $validated)
                ? $validated['owner_name']
                : $adminRestaurant->owner_name,
            'area' => $validated['location'] ?? $adminRestaurant->area,
            'address_line' => array_key_exists('address_line', $validated)
                ? $validated['address_line']
                : $adminRestaurant->address_line,
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
        $listSvc = new AdminUserListService;
        $data = $listSvc->paginatedForAdmin($request);
        $stats = $listSvc->stats();

        return view('surprisebite.admin.users', [
            'users' => $data['users'],
            'q' => $data['q'],
            'roleFilter' => $data['roleFilter'],
            'orderCounts' => $data['orderCounts'],
            'stats' => $stats,
            'activeListFilter' => $data['activeListFilter'],
        ]);
    }

    public function storeUser(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:customer,seller,mitra'],
            'is_active' => ['required', 'in:0,1'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' => (bool) (int) $validated['is_active'],
        ];

        if ($validated['role'] === 'mitra' && Schema::hasColumn('users', 'mitra_approval_status')) {
            $payload['mitra_approval_status'] = 'approved';
            $payload['mitra_approval_decided_at'] = now();
        }

        User::create($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Pengguna berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.users')->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $actor = $request->user();
        $isSelf = $actor && $actor->id === $user->id;

        if ($user->role === 'admin' && ! $isSelf) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tidak dapat mengedit akun admin lain.',
                ], 403);
            }

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

        $previousRole = (string) $user->role;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if (isset($validated['role']) && $user->role !== 'admin') {
            $user->role = $validated['role'];
        }

        $newRole = (string) $user->role;
        if (
            $previousRole !== 'mitra'
            && $newRole === 'mitra'
            && Schema::hasColumn('users', 'mitra_approval_status')
        ) {
            $user->mitra_approval_status = 'pending';
            $user->mitra_approval_decided_at = null;
        }

        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Pengguna diperbarui.',
            ]);
        }

        return redirect()->route('admin.users')->with('status', 'Pengguna diperbarui.');
    }

    public function updateMitraApproval(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->role !== 'mitra') {
            abort(404);
        }

        $validated = $request->validate([
            'mitra_approval_status' => ['required', 'string', 'in:pending,approved,rejected'],
        ]);

        if (! Schema::hasColumn('users', 'mitra_approval_status')) {
            abort(404);
        }

        $next = $validated['mitra_approval_status'];

        $user->mitra_approval_status = $next;
        $user->mitra_approval_decided_at = in_array($next, ['approved', 'rejected'], true) ? now() : null;
        $user->save();

        $msg = 'Status persetujuan mitra untuk '.$user->name.' telah diperbarui.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $msg,
                'user' => [
                    'id' => $user->id,
                    'mitra_approval_status' => $user->mitra_approval_status,
                ],
            ]);
        }

        // Redirect back to restaurants page if coming from there, otherwise to users page
        $referer = $request->header('referer');
        if ($referer && str_contains($referer, 'admin/restaurants')) {
            return redirect()->route('admin.restaurants')->with('status', $msg);
        }

        return redirect()->route('admin.users')->with('status', $msg);
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

    public function destroyUser(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($user->id === $request->user()?->id) {
            $msg = 'Tidak dapat menghapus akun Anda sendiri.';

            return $request->expectsJson()
                ? response()->json(['message' => $msg], 422)
                : redirect()->route('admin.users')->with('status', $msg);
        }

        if ($user->role === 'admin') {
            $msg = 'Tidak dapat menghapus akun admin.';

            return $request->expectsJson()
                ? response()->json(['message' => $msg], 422)
                : redirect()->route('admin.users')->with('status', $msg);
        }

        if (Schema::hasTable('checkout_orders') && CheckoutOrder::where('customer_email', $user->email)->exists()) {
            $msg = 'User memiliki riwayat pesanan di sistem. Nonaktifkan akun alih-alih menghapus, atau hubungi tim teknis.';

            return $request->expectsJson()
                ? response()->json(['message' => $msg], 422)
                : redirect()->route('admin.users')->with('status', $msg);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            $msg = 'Tidak dapat menghapus user karena masih terhubung data lain.';
            if (str_contains(strtolower($e->getMessage()), 'foreign')) {
                $msg = 'Tidak dapat menghapus: data user masih dipakai di tabel lain.';
            }

            return $request->expectsJson()
                ? response()->json(['message' => $msg], 422)
                : redirect()->route('admin.users')->with('status', $msg);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Pengguna dihapus.',
            ]);
        }

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
