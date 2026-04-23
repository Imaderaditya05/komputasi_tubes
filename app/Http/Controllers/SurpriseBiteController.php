<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CheckoutOrder;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\User;
use App\Services\CartAfterPaymentService;
use App\Services\MenuStockService;
use App\Services\CatalogRepository;
use App\Services\ImpactMetricsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SurpriseBiteController extends Controller
{
    private function defaultCheckoutState(): array
    {
        return [
            'method' => 'delivery',
            'address' => '',
            'payment' => 'ewallet',
            'order_id' => null,
        ];
    }

    private function getCheckoutState(Request $request, string $slug): array
    {
        $state = $request->session()->get("checkout.$slug", []);

        if (!is_array($state)) {
            $state = [];
        }

        return array_replace($this->defaultCheckoutState(), $state);
    }

    /**
     * Katalog home/browse — dari admin (DB) atau bawaan (CatalogRepository).
     */
    private function catalog(): array
    {
        return app(CatalogRepository::class)->getCatalog();
    }

    private function moneyIDR(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    private function findBox(string $slug): array
    {
        $catalog = $this->catalog();

        foreach ($catalog['boxes'] as $box) {
            if ($box['slug'] === $slug) {
                return $box;
            }
        }

        abort(404);
    }

    private function findRestaurant(string $id): array
    {
        $catalog = $this->catalog();

        foreach ($catalog['restaurants'] as $r) {
            if ((string) ($r['id'] ?? '') === (string) $id) {
                return $r;
            }
        }

        abort(404);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, array<string, mixed>>
     */
    private function catalogRestaurantLookup(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $map[(string) ($row['id'] ?? '')] = $row;
        }

        return $map;
    }

    private function boxFilterKeyForBrowse(array $box): string
    {
        $fk = (string) ($box['filter_key'] ?? '');
        if ($fk !== '') {
            return $fk;
        }

        $c = strtolower((string) ($box['category'] ?? ''));

        return match (true) {
            str_contains($c, 'bakery') || str_contains($c, 'roti') => 'bakery',
            str_contains($c, 'italian') || str_contains($c, 'pizza') => 'italian',
            str_contains($c, 'japan') || str_contains($c, 'sushi') => 'japanese',
            str_contains($c, 'cafe') || str_contains($c, 'coffee') || str_contains($c, 'kopi') => 'cafe',
            str_contains($c, 'healthy') || str_contains($c, 'salad') => 'healthy',
            default => 'restaurant',
        };
    }

    private function boxMatchesSearchQuery(string $q, array $box, ?array $restaurant): bool
    {
        $q = preg_replace('/\s+/u', ' ', trim($q));
        if ($q === '') {
            return true;
        }

        $needle = mb_strtolower($q, 'UTF-8');
        $parts = [
            $box['title'] ?? '',
            $box['description'] ?? '',
            $box['category_label'] ?? '',
            $box['category'] ?? '',
            $box['filter_key'] ?? '',
            $box['badge'] ?? '',
            is_array($restaurant) ? (string) ($restaurant['name'] ?? '') : '',
            is_array($restaurant) ? (string) ($restaurant['subtitle'] ?? '') : '',
            is_array($restaurant) ? (string) ($restaurant['area'] ?? '') : '',
            is_array($restaurant) ? (string) ($restaurant['city'] ?? '') : '',
        ];
        $haystack = mb_strtolower(implode(' ', $parts), 'UTF-8');

        return str_contains($haystack, $needle);
    }

    public function home(Request $request): View
    {
        $catalog = $this->catalog();

        $selectedCategory = (string) $request->query('category', '');
        $q = trim((string) $request->query('q', ''));

        $lookup = $this->catalogRestaurantLookup($catalog['restaurants']);

        $boxes = array_values(array_filter($catalog['boxes'], function (array $box) use ($selectedCategory, $q, $lookup) {
            if ($selectedCategory !== '' && ($box['category'] ?? '') !== $selectedCategory) {
                return false;
            }

            $rid = (string) ($box['restaurant_id'] ?? '');
            $restaurant = $lookup[$rid] ?? null;

            return $this->boxMatchesSearchQuery($q, $box, $restaurant);
        }));

        return view('surprisebite.home', [
            ...$catalog,
            ...$this->getImpactMetrics(),
            'boxes' => $boxes,
            'catalog_boxes' => $catalog['boxes'],
            'selectedCategory' => $selectedCategory,
            'q' => $q,
            'catalogHash' => app(CatalogRepository::class)->catalogFingerprint(),
            'money' => fn (int $n) => $this->moneyIDR($n),
        ]);
    }

    public function browse(Request $request): View
    {
        $catalog = $this->catalog();

        $filterType = (string) ($request->filled('ft') ? $request->query('ft') : $request->query('lt', 'all'));
        $allowedFt = ['all', 'bakery', 'restaurant', 'healthy', 'cafe', 'italian', 'japanese'];
        if (! in_array($filterType, $allowedFt, true)) {
            $filterType = 'all';
        }

        $maxPrice = (int) $request->query('max_price', 50000);
        $maxPrice = max(10000, min(200000, $maxPrice));

        $sort = (string) $request->query('sort', 'nearest');
        if (!in_array($sort, ['nearest', 'price', 'rating'], true)) {
            $sort = 'nearest';
        }

        $q = trim((string) $request->query('q', ''));

        $lookup = $this->catalogRestaurantLookup($catalog['restaurants']);

        $boxes = array_values(array_filter($catalog['boxes'], function (array $box) use ($filterType, $maxPrice, $q, $lookup): bool {
            if ($filterType !== 'all' && $this->boxFilterKeyForBrowse($box) !== $filterType) {
                return false;
            }

            if (($box['price'] ?? 0) > $maxPrice) {
                return false;
            }

            $rid = (string) ($box['restaurant_id'] ?? '');
            $restaurant = $lookup[$rid] ?? null;

            return $this->boxMatchesSearchQuery($q, $box, $restaurant);
        }));

        usort($boxes, static function (array $a, array $b) use ($sort): int {
            return match ($sort) {
                'price' => $a['price'] <=> $b['price'],
                'rating' => $b['card_rating'] <=> $a['card_rating'],
                default => $a['distance_km'] <=> $b['distance_km'],
            };
        });

        $filterLabels = [
            'all' => 'All',
            'bakery' => 'Bakery',
            'restaurant' => 'Restaurant',
            'healthy' => 'Healthy',
            'cafe' => 'Cafe',
            'italian' => 'Italian',
            'japanese' => 'Japanese',
        ];

        return view('surprisebite.browse', [
            ...$catalog,
            'boxes' => $boxes,
            'restaurant_lookup' => $lookup,
            'filterType' => $filterType,
            'maxPrice' => $maxPrice,
            'sort' => $sort,
            'q' => $q,
            'filterLabels' => $filterLabels,
            'catalogHash' => app(CatalogRepository::class)->catalogFingerprint(),
            'money' => fn (int $n) => $this->moneyIDR($n),
        ]);
    }

    public function impact(): View
    {
        return view('surprisebite.impact', $this->getImpactMetrics());
    }

    public function about(): View
    {
        return view('surprisebite.about', $this->getImpactMetrics());
    }

    public function box(string $slug): View
    {
        $box = $this->findBox($slug);
        $restaurant = $this->findRestaurant($box['restaurant_id']);

        return view('surprisebite.box', [
            'box' => $box,
            'restaurant' => $restaurant,
            'money' => fn (int $n) => $this->moneyIDR($n),
        ]);
    }

    public function checkoutDelivery(Request $request, string $slug): View
    {
        $box = $this->findBox($slug);
        $restaurant = $this->findRestaurant($box['restaurant_id']);

        $state = $this->getCheckoutState($request, $slug);

        return view('surprisebite.checkout.delivery', [
            'box' => $box,
            'restaurant' => $restaurant,
            'state' => $state,
            'money' => fn (int $n) => $this->moneyIDR($n),
        ]);
    }

    public function checkoutDeliverySubmit(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'method' => ['required', 'in:pickup,delivery'],
            'address' => ['nullable', 'string', 'max:200'],
        ]);

        if ($validated['method'] === 'delivery' && trim((string) ($validated['address'] ?? '')) === '') {
            return back()->withErrors(['address' => 'Alamat wajib diisi untuk Delivery.'])->withInput();
        }

        $request->session()->put("checkout.$slug.method", $validated['method']);
        $request->session()->put("checkout.$slug.address", (string) ($validated['address'] ?? ''));

        return redirect()->route('checkout.payment', ['slug' => $slug]);
    }

    public function checkoutPayment(Request $request, string $slug): View
    {
        $box = $this->findBox($slug);
        $restaurant = $this->findRestaurant($box['restaurant_id']);

        $state = $this->getCheckoutState($request, $slug);

        return view('surprisebite.checkout.payment', [
            'box' => $box,
            'restaurant' => $restaurant,
            'state' => $state,
            'money' => fn (int $n) => $this->moneyIDR($n),
        ]);
    }

    public function checkoutPay(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'payment' => ['required', 'in:va,cod'],
        ]);

        $box = $this->findBox($slug);
        $restaurant = $this->findRestaurant($box['restaurant_id']);
        $state = $this->getCheckoutState($request, $slug);

        $orderId = $this->generateUniquePublicOrderId();

        $customerId = 0;
        $customerEmail = '';

        $laravelUser = Auth::user();
        if ($laravelUser && $laravelUser->role === 'customer') {
            $customer = Customer::firstOrCreate(
                ['email' => $laravelUser->email],
                [
                    'name' => $laravelUser->name,
                    'password' => $laravelUser->getAuthPassword(),
                ]
            );
            $customerId = (int) $customer->id;
            $customerEmail = $laravelUser->email;
        } else {
            $auth = $request->session()->get('auth', []);
            $customerId = (int) ($auth['id'] ?? 0);
            $customerEmail = (string) ($auth['email'] ?? '');
        }

        if ($customerId === 0 || $customerEmail === '') {
            return redirect()
                ->route('login')
                ->with('status', 'Silakan login sebagai pelanggan untuk menyelesaikan pesanan.');
        }

        $qty = $this->cartQuantityForBoxSlug($laravelUser, $slug);
        $mitraMenuId = MenuStockService::parseMitraMenuId($slug);
        if ($mitraMenuId !== null) {
            $menu = Menu::query()->find($mitraMenuId);
            if (! $menu || (int) $menu->stock < $qty) {
                return back()->withErrors(['payment' => 'Stok tidak cukup untuk jumlah pesanan ini.'])->withInput();
            }
        }

        $unitPrice = (int) $box['price'];
        $amountTotal = $unitPrice * $qty;

        $order = CheckoutOrder::create([
            'public_order_id' => $orderId,
            'customer_id' => $customerId,
            'customer_email' => $customerEmail,
            'box_slug' => $slug,
            'box_title' => $box['title'],
            'restaurant_name' => $restaurant['name'],
            'amount_idr' => $amountTotal,
            'item_quantity' => $qty,
            'payment_method' => $validated['payment'],
            'fulfillment_method' => $state['method'],
            'delivery_address' => $state['method'] === 'delivery'
                ? (trim((string) ($state['address'] ?? '')) ?: null)
                : null,
            'restaurant_latitude' => isset($restaurant['latitude']) ? (float) $restaurant['latitude'] : null,
            'restaurant_longitude' => isset($restaurant['longitude']) ? (float) $restaurant['longitude'] : null,
            'payment_status' => $validated['payment'] === 'cod' ? 'PENDING_COD' : 'PENDING',
            'pickup_time' => $box['pickup_time'] ?? null,
            'fulfillment_status' => $validated['payment'] === 'cod'
                ? 'pending_confirmation'
                : 'awaiting_payment',
        ]);

        $request->session()->put("checkout.$slug.payment", $validated['payment']);
        $request->session()->put("checkout.$slug.order_id", $orderId);

        if ($validated['payment'] === 'cod') {
            MenuStockService::applyForOrder($order);
            CartAfterPaymentService::clearForOrder($order);

            return redirect()->route('checkout.success', ['slug' => $slug]);
        }

        // Redirect ke Midtrans payment
        return redirect()->route('payment.checkout', ['order_id' => $orderId]);
    }

    public function checkoutSuccess(Request $request, string $slug): View
    {
        $box = $this->findBox($slug);
        $restaurant = $this->findRestaurant($box['restaurant_id']);

        $state = $this->getCheckoutState($request, $slug);

        return view('surprisebite.checkout.success', [
            'box' => $box,
            'restaurant' => $restaurant,
            'state' => $state,
            'money' => fn (int $n) => $this->moneyIDR($n),
        ]);
    }

    public function adminDashboard(): View
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

        return view('surprisebite.admin.dashboard', [
            'totalCustomers' => $totalCustomers,
            'totalTransactions' => $totalTransactions,
            'ordersToday' => $ordersToday,
            'revenueToday' => $revenueToday,
            'recentOrders' => $recentOrders,
            'money' => fn (int $n) => $this->moneyIDR($n),
            'paymentLabel' => $paymentLabel,
        ]);
    }

    /**
     * Jumlah unit di keranjang untuk slug ini (checkout single-line per box).
     */
    private function cartQuantityForBoxSlug(?User $user, string $slug): int
    {
        if (! $user || $user->role !== 'customer') {
            return 1;
        }

        $cart = Cart::query()->where('user_id', $user->id)->first();
        if (! $cart) {
            return 1;
        }

        $item = $cart->items()->where('box_slug', $slug)->first();
        if (! $item) {
            return 1;
        }

        return max(1, (int) $item->quantity);
    }

    private function generateUniquePublicOrderId(): string
    {
        for ($i = 0; $i < 10; $i++) {
            $id = 'ORD-' . str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
            if (! CheckoutOrder::where('public_order_id', $id)->exists()) {
                return $id;
            }
        }

        return 'ORD-' . str_replace('.', '', uniqid('', true));
    }

    public function adminImpact(): View
    {
        return view('surprisebite.admin.impact', $this->getImpactMetrics());
    }

    public function getImpactMetrics(): array
    {
        $svc = app(ImpactMetricsService::class);
        $meals = $svc->totalMealsSaved();
        $wasteKg = $svc->foodWasteReducedKg();
        $wasteTons = $svc->foodWasteReducedTons();
        $wd = $svc->foodWasteDisplay();
        $activeUsers = $svc->activeRescueUsersCount();
        $year = (int) date('Y');
        $monthlyTrend = $svc->monthlyTrendForYear($year);

        $wasteDisplay = [
            'value' => $wd['value'],
            'decimals' => $wd['decimals'],
            'unit' => $wd['unit'],
        ];

        return [
            'impactMeals' => $meals,
            'impactWasteValue' => $wd['value'],
            'impactWasteDecimals' => $wd['decimals'],
            'impactWasteUnit' => $wd['unit'],
            'impactActiveUsers' => $activeUsers,

            'mealsSaved' => $meals,
            'wasteDisplay' => $wasteDisplay,
            'activeUsers' => $activeUsers,
            'wasteKg' => $wasteKg,
            'wasteTons' => $wasteTons,
            'monthlyTrend' => $monthlyTrend,
            'trendYear' => $svc->trendYearLabel($year),
        ];
    }
}

