<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Concerns\AssertMitraRestaurantCheckoutOrder;
use App\Http\Controllers\Controller;
use App\Models\CheckoutOrder;
use App\Models\Restaurant;
use App\Services\PickupValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MitraPickupValidationController extends Controller
{
    use AssertMitraRestaurantCheckoutOrder;

    public function __construct(
        private PickupValidationService $pickupValidationService,
    ) {}

    private function refreshPickupState(CheckoutOrder $order): CheckoutOrder
    {
        $this->pickupValidationService->ensurePickupValidationWindowStarted($order);
        $order->refresh();
        $this->pickupValidationService->syncExpiredIfMissedDeadline($order);

        $fresh = $order->fresh();

        return $fresh instanceof CheckoutOrder ? $fresh : $order;
    }

    public function index(Request $request, Restaurant $restaurant): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);

        $slugs = $restaurant->menus()->pluck('id')->map(fn (int $id) => 'mitra-menu-'.$id)->all();

        $orders = collect();
        if ($slugs !== []) {
            $orders = CheckoutOrder::query()
                ->whereIn('box_slug', $slugs)
                ->where('fulfillment_method', 'pickup')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        $orders->transform(fn (CheckoutOrder $o): CheckoutOrder => $this->refreshPickupState($o));

        return view('mitra.pickup.checkout-pickups', [
            'restaurant' => $restaurant,
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Restaurant $restaurant, string $publicOrderId): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);

        /** @var CheckoutOrder|null $order */
        $order = CheckoutOrder::query()
            ->where('public_order_id', $publicOrderId)
            ->first();

        if ($order === null) {
            abort(404);
        }

        $this->assertCheckoutOrderBelongsToRestaurant($order, $restaurant);

        $order = $this->refreshPickupState($order);

        return view('mitra.pickup.checkout-pickup-show', [
            'restaurant' => $restaurant,
            'order' => $order,
        ]);
    }

    public function live(Request $request, Restaurant $restaurant, string $publicOrderId): JsonResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);

        /** @var CheckoutOrder $order */
        $order = CheckoutOrder::query()
            ->where('public_order_id', $publicOrderId)
            ->firstOrFail();

        $this->assertCheckoutOrderBelongsToRestaurant($order, $restaurant);

        $order = $this->refreshPickupState($order);

        return response()->json([
            'success' => true,
            'pickup_live' => $this->pickupValidationService->livePayloadForMitra($order),
        ]);
    }

    public function submit(Request $request, Restaurant $restaurant, string $publicOrderId): JsonResponse|RedirectResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'pickup_validation_note' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var CheckoutOrder $order */
        $order = CheckoutOrder::query()
            ->where('public_order_id', $publicOrderId)
            ->firstOrFail();

        $this->assertCheckoutOrderBelongsToRestaurant($order, $restaurant);

        $note = trim((string) ($validated['pickup_validation_note'] ?? ''));

        $order = $this->pickupValidationService->submitForMitra(
            $order,
            $restaurant,
            $request->user(),
            $note,
        );

        $payload = [
            'success' => true,
            'message' => 'Validasi pickup berhasil. Pesanan ditandai selesai.',
            'order' => [
                'public_order_id' => $order->public_order_id,
                'fulfillment_status' => $order->fulfillment_status,
                'pickup_validation_status' => $order->pickup_validation_status,
                'pickup_validation_note' => $order->pickup_validation_note,
                'pickup_validated_at' => $order->pickup_validated_at?->toIso8601String(),
            ],
            'pickup_live' => $this->pickupValidationService->livePayloadForMitra($order),
        ];

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json($payload);
        }

        return redirect()
            ->route('mitra.checkout-pickups.show', ['restaurant' => $restaurant, 'publicOrderId' => $order->public_order_id])
            ->with('status', $payload['message']);
    }
}
