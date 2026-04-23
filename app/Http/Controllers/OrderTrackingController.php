<?php

namespace App\Http\Controllers;

use App\Models\CheckoutOrder;
use App\Services\OrderMapLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
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
        $this->authorizeOrder($request, $order);

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

        return view('orders.track', [
            'order' => $order,
            'money' => $money,
            'demoEnabled' => filter_var(env('ORDER_TRACKING_DEMO', true), FILTER_VALIDATE_BOOLEAN),
            'fulfillmentBadge' => [OrderHistoryController::class, 'formatFulfillmentBadge'],
            'fulfillmentBadgeClass' => [OrderHistoryController::class, 'formatFulfillmentBadgeClass'],
            'mapPayload' => $mapPayload,
        ]);
    }

    public function demoAdvance(Request $request, string $publicOrderId): RedirectResponse
    {
        if (! filter_var(env('ORDER_TRACKING_DEMO', true), FILTER_VALIDATE_BOOLEAN)) {
            abort(404);
        }

        $order = CheckoutOrder::where('public_order_id', $publicOrderId)->first();
        if (! $order) {
            return redirect()
                ->route('orders.index')
                ->with([
                    'status' => 'Pesanan '.$publicOrderId.' tidak ditemukan.',
                ]);
        }
        $this->authorizeOrder($request, $order);

        $current = $order->fulfillment_status ?? 'awaiting_payment';
        $idx = array_search($current, self::DEMO_SEQUENCE, true);

        if ($idx === false) {
            $order->update(['fulfillment_status' => 'pending_confirmation']);

            return back()->with('status', 'Status diperbarui (demo).');
        }

        if ($idx < count(self::DEMO_SEQUENCE) - 1) {
            $order->update(['fulfillment_status' => self::DEMO_SEQUENCE[$idx + 1]]);
        }

        return back()->with('status', 'Status diperbarui (demo).');
    }

    private function authorizeOrder(Request $request, CheckoutOrder $order): void
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer' || $order->customer_email !== $user->email) {
            abort(403);
        }
    }
}
