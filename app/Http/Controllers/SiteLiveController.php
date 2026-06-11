<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CheckoutOrder;
use App\Models\CustomerNotification;
use App\Services\CatalogRepository;
use App\Services\OrderMapLocationService;
use App\Services\PickupValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SiteLiveController extends Controller
{
    public function __construct(
        private OrderMapLocationService $orderMapLocation,
        private PickupValidationService $pickupValidationService,
    ) {}

    /**
     * Polling untuk home/browse — reload saat katalog mitra/admin berubah.
     */
    public function catalogHash(Request $request): JsonResponse
    {
        return response()->json([
            'hash' => app(CatalogRepository::class)->catalogFingerprint(),
        ]);
    }

    public function cart(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            return response()->json(['quantity' => 0]);
        }

        $cart = Cart::query()->where('user_id', $user->id)->first();
        $qty = $cart ? (int) $cart->items()->sum('quantity') : 0;

        return response()->json(['quantity' => $qty]);
    }

    /**
     * Polling untuk halaman riwayat pesanan — status pembayaran terbaru tanpa reload penuh.
     */
    public function orders(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            return response()->json(['orders' => []]);
        }

        $rows = CheckoutOrder::query()
            ->where('customer_email', $user->email)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['public_order_id', 'payment_status', 'payment_method', 'amount_idr', 'fulfillment_status', 'fulfillment_method', 'pickup_validation_status', 'updated_at']);

        $orders = $rows->map(static function (CheckoutOrder $o): array {
            return [
                'public_order_id' => $o->public_order_id,
                'payment_status' => $o->payment_status,
                'payment_method' => $o->payment_method,
                'amount_idr' => (int) $o->amount_idr,
                'fulfillment_status' => $o->fulfillment_status,
                'fulfillment_method' => $o->fulfillment_method,
                'pickup_validation_status' => $o->pickup_validation_status,
                'updated_at' => $o->updated_at?->toIso8601String(),
            ];
        });

        return response()->json(['orders' => $orders]);
    }

    /**
     * Satu pesanan — untuk halaman tracking (polling status fulfillment).
     */
    public function order(Request $request, string $publicOrderId): JsonResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $order = CheckoutOrder::query()
            ->where('public_order_id', $publicOrderId)
            ->where('customer_email', $user->email)
            ->first();

        if (! $order) {
            return response()->json(['error' => 'not_found'], 404);
        }

        if ($order->fulfillment_method === 'pickup') {
            $this->pickupValidationService->ensurePickupValidationWindowStarted($order);
            $order->refresh();
            $this->pickupValidationService->syncExpiredIfMissedDeadline($order);
            $order->refresh();
        }

        $pickupPayload = null;
        if ($order->fulfillment_method === 'pickup') {
            $pickupPayload = $this->pickupValidationService->livePayloadForCustomer($order);
        }

        return response()->json([
            'public_order_id' => $order->public_order_id,
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
            'fulfillment_method' => $order->fulfillment_method,
            'pickup_validation_status' => $order->pickup_validation_status,
            'pickup_live' => $pickupPayload,
            'updated_at' => $order->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Polling lokasi toko + kurir untuk peta lacak (realtime).
     */
    public function orderTracking(Request $request, string $publicOrderId): JsonResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $order = CheckoutOrder::query()
            ->where('public_order_id', $publicOrderId)
            ->where('customer_email', $user->email)
            ->first();

        if (! $order) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $map = $this->orderMapLocation->resolveForCheckoutOrder($order);

        $cLat = $this->orderMapLocation->normalizeCoordinate($order->courier_latitude ?? null);
        $cLng = $this->orderMapLocation->normalizeCoordinate($order->courier_longitude ?? null);
        $courierOk = $this->orderMapLocation->coordinatesPlausibleIndonesia($cLat, $cLng);

        return response()->json([
            'public_order_id' => $order->public_order_id,
            'fulfillment_status' => $order->fulfillment_status,
            'fulfillment_method' => $order->fulfillment_method,
            'restaurant_lat' => $map['restaurantLat'],
            'restaurant_lng' => $map['restaurantLng'],
            'courier_lat' => $courierOk ? $cLat : null,
            'courier_lng' => $courierOk ? $cLng : null,
            'courier_updated_at' => $order->courier_updated_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Polling notifikasi pelanggan (badge header + lista halaman notifikasi tanpa reload penuh).
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer' || ! Schema::hasTable('customer_notifications')) {
            return response()->json([
                'fingerprint' => '',
                'unread_count' => 0,
                'notifications' => [],
                'tracker_url_tpl' => url('/orders/__ORDER__/track'),
            ]);
        }

        $notifications = CustomerNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(80)
            ->get([
                'id',
                'type',
                'title',
                'body',
                'public_order_id',
                'read_at',
                'created_at',
                'updated_at',
            ]);

        $unreadCount = CustomerNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $maxId = (int) $notifications->max('id');
        $latestUpdated = optional($notifications->first())->updated_at;

        $fingerprintRaw = implode('|', [
            (string) $unreadCount,
            (string) $maxId,
            $latestUpdated?->toIso8601String() ?? '',
            (string) $notifications->count(),
        ]);

        $payloadNotifications = $notifications->map(static function (CustomerNotification $n): array {
            return [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'public_order_id' => $n->public_order_id,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
                'updated_at' => $n->updated_at?->toIso8601String(),
                'read' => $n->read_at !== null,
            ];
        })->values()->all();

        return response()->json([
            'fingerprint' => hash('sha256', $fingerprintRaw),
            'unread_count' => $unreadCount,
            'notifications' => $payloadNotifications,
            'tracker_url_tpl' => url('/orders/__ORDER__/track'),
        ]);
    }
}
