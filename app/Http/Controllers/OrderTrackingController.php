<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCustomerCheckoutOrder;
use App\Models\CheckoutOrder;
use App\Services\MitraMenuRatingAggregator;
use App\Services\OrderMapLocationService;
use App\Services\PickupValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    use AuthorizesCustomerCheckoutOrder;

    /** @var list<string> */
    private const DEMO_SEQUENCE = [
        'awaiting_payment',
        'pending_confirmation',
        'received',
        'preparing',
        'ready',
        'completed',
    ];

    public function __construct(
        private OrderMapLocationService $orderMapLocation,
        private PickupValidationService $pickupValidationService,
    ) {}

    public function show(Request $request, string $publicOrderId): View|RedirectResponse
    {
        $order = CheckoutOrder::where('public_order_id', $publicOrderId)->first();
        if (! $order) {
            return redirect()
                ->route('orders.index')
                ->with([
                    'status' => 'Pesanan '.$publicOrderId.' tidak ditemukan. Jika Anda baru memindahkan ke MySQL atau mengosongkan database, data lama tidak ikut — impor dari backup SQLite atau buat pesanan baru.',
                ]);
        }
        $this->authorizeCustomerCheckoutOrder($request, $order);

        // Jendela validasi pickup mitra — persiapkan countdown & status kedaluwarsa (lazy).
        if ($order->fulfillment_method === 'pickup') {
            $this->pickupValidationService->ensurePickupValidationWindowStarted($order);
            $order->refresh();
            $this->pickupValidationService->syncExpiredIfMissedDeadline($order);
            $order->refresh();
        }

        $money = fn (int $n): string => 'Rp '.number_format($n, 0, ',', '.');

        $map = $this->orderMapLocation->resolveForCheckoutOrder($order);

        $apiKey = (string) config('services.google_maps.key', '');
        $fs = $order->fulfillment_status ?? 'awaiting_payment';
        $isDelivery = $order->fulfillment_method === 'delivery';
        $deliveryAddr = trim((string) ($order->delivery_address ?? ''));
        $mapShowUi = $fs !== 'awaiting_payment' && (
            (! $isDelivery) || ($isDelivery && $deliveryAddr !== '')
        );
        $mapEnabled = $mapShowUi && $apiKey !== '';

        $liveTracking = $isDelivery && in_array($fs, ['received', 'preparing', 'ready'], true);

        $mapPayload = [
            'showUi' => $mapShowUi,
            'enabled' => $mapEnabled,
            'apiKey' => $apiKey,
            'mode' => $isDelivery ? 'delivery' : 'pickup',
            'restaurantLat' => $map['restaurantLat'],
            'restaurantLng' => $map['restaurantLng'],
            'restaurantName' => $order->restaurant_name,
            'mapQuery' => $map['mapQuery'],
            'mapQueryAlternates' => $map['mapQueryAlternates'],
            'deliveryAddress' => $isDelivery ? $deliveryAddr : null,
            'fulfillmentStatus' => $fs,
            'liveTracking' => $liveTracking,
            'trackingPollUrl' => $mapShowUi && $mapEnabled
                ? route('api.live.order.tracking', ['publicOrderId' => $order->public_order_id])
                : null,
            'pollIntervalMs' => 3000,
            'courierLat' => $this->orderMapLocation->normalizeCoordinate($order->courier_latitude ?? null),
            'courierLng' => $this->orderMapLocation->normalizeCoordinate($order->courier_longitude ?? null),
            /** Pusat Indonesia (Jakarta) jika geocoding gagal — peta tetap tampil. */
            'fallbackLat' => -6.2088,
            'fallbackLng' => 106.8456,
        ];

        $demoEnabled = filter_var(env('ORDER_TRACKING_DEMO', true), FILTER_VALIDATE_BOOLEAN);
        $demoAuto = $demoEnabled && $fs !== 'completed';

        return view('orders.track', [
            'order' => $order,
            'money' => $money,
            'demoEnabled' => $demoEnabled,
            'demoAuto' => $demoAuto,
            'fulfillmentBadge' => [OrderHistoryController::class, 'formatFulfillmentBadge'],
            'fulfillmentBadgeClass' => [OrderHistoryController::class, 'formatFulfillmentBadgeClass'],
            'mapPayload' => $mapPayload,
        ]);
    }

    public function demoAdvance(Request $request, string $publicOrderId): RedirectResponse|JsonResponse
    {
        if (! filter_var(env('ORDER_TRACKING_DEMO', true), FILTER_VALIDATE_BOOLEAN)) {
            abort(404);
        }

        $order = CheckoutOrder::where('public_order_id', $publicOrderId)->first();
        if (! $order) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'error' => 'Pesanan tidak ditemukan.'], 404);
            }

            return redirect()
                ->route('orders.index')
                ->with([
                    'status' => 'Pesanan '.$publicOrderId.' tidak ditemukan.',
                ]);
        }
        $this->authorizeCustomerCheckoutOrder($request, $order);

        $current = $order->fulfillment_status ?? 'awaiting_payment';

        if ($current === 'completed') {
            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'fulfillment_status' => 'completed',
                    'completed' => true,
                ]);
            }

            return back()->with('status', 'Pesanan sudah selesai.');
        }

        $idx = array_search($current, self::DEMO_SEQUENCE, true);

        if ($idx === false) {
            $order->update(['fulfillment_status' => 'pending_confirmation']);
        } elseif ($idx < count(self::DEMO_SEQUENCE) - 1) {
            $order->update(['fulfillment_status' => self::DEMO_SEQUENCE[$idx + 1]]);
        }

        $order->refresh();

        // Pesanan pickup berbayar: kolom countdown validasi dapat dimulai lebih awal di alur restoran.
        if ($order->fulfillment_method === 'pickup') {
            $this->pickupValidationService->ensurePickupValidationWindowStarted($order);
            $order->refresh();
            $this->pickupValidationService->syncExpiredIfMissedDeadline($order);
            $order->refresh();
        }

        if ($request->wantsJson()) {
            $fs = (string) ($order->fulfillment_status ?? '');

            return response()->json([
                'ok' => true,
                'fulfillment_status' => $fs,
                'completed' => $fs === 'completed',
            ]);
        }

        return back()->with('status', 'Status diperbarui (demo).');
    }

    public function submitReview(Request $request, string $publicOrderId): RedirectResponse
    {
        $order = CheckoutOrder::query()->where('public_order_id', $publicOrderId)->first();
        if (! $order) {
            return redirect()
                ->route('orders.index')
                ->with(['status' => 'Pesanan tidak ditemukan.']);
        }

        $this->authorizeCustomerCheckoutOrder($request, $order);

        if (($order->fulfillment_status ?? '') !== 'completed') {
            return back()->withErrors(['rating' => 'Penilaian hanya bisa setelah pesanan selesai.']);
        }

        if ($order->reviewed) {
            return back()->with('status', 'Anda sudah memberikan penilaian untuk pesanan ini.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $comment = isset($validated['comment']) ? trim((string) $validated['comment']) : '';
        $order->update([
            'reviewed' => true,
            'customer_rating' => $validated['rating'],
            'customer_review_comment' => $comment !== '' ? $comment : null,
        ]);

        $order->refresh();
        app(MitraMenuRatingAggregator::class)->applyOrderReview($order);

        return back()->with('status', 'Terima kasih atas penilaian Anda!');
    }
}
