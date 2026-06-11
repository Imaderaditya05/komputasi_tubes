<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Concerns\AssertMitraRestaurantCheckoutOrder;
use App\Http\Controllers\Controller;
use App\Models\CheckoutOrder;
use App\Models\Restaurant;
use App\Services\MitraCheckoutOrderStatusService;
use App\Services\MitraRestaurantFeedbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    use AssertMitraRestaurantCheckoutOrder;

    public function index(Request $request, Restaurant $restaurant): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $feedback = app(MitraRestaurantFeedbackService::class);

        $orders = $feedback
            ->checkoutOrdersQuery($restaurant)
            ->limit(120)
            ->get();

        return view('mitra.orders.index', [
            'restaurant' => $restaurant,
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Restaurant $restaurant, string $public_order_id): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $order = CheckoutOrder::query()->where('public_order_id', $public_order_id)->firstOrFail();

        $this->assertCheckoutOrderBelongsToRestaurant($order, $restaurant);

        return view('mitra.orders.show', [
            'restaurant' => $restaurant,
            'order' => $order,
        ]);
    }

    public function updateStatus(
        Request $request,
        Restaurant $restaurant,
        string $public_order_id,
        MitraCheckoutOrderStatusService $statusService,
    ): RedirectResponse {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $validated = $request->validate([
            'phase' => ['required', 'string', 'in:pending,paid,completed'],
        ]);

        $order = CheckoutOrder::query()->where('public_order_id', $public_order_id)->firstOrFail();
        $this->assertCheckoutOrderBelongsToRestaurant($order, $restaurant);

        $statusService->applyPhase($restaurant, $order, $validated['phase'], $request->user());

        return redirect()
            ->route('restaurants.orders.show', [$restaurant, $public_order_id])
            ->with('status', 'Status pesanan telah diperbarui.');
    }
}
