<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\MitraOrder;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $orders = $restaurant->orders()->latest()->get();
        return view('mitra.orders.index', compact('restaurant', 'orders'));
    }

    public function show(Request $request, Restaurant $restaurant, MitraOrder $order): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');
        abort_unless($order->restaurant_id === $restaurant->id, 404, 'Not Found');

        return view('mitra.orders.show', compact('restaurant', 'order'));
    }

    public function update(Request $request, Restaurant $restaurant, MitraOrder $order): RedirectResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');
        abort_unless($order->restaurant_id === $restaurant->id, 404, 'Not Found');

        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return redirect()->route('restaurants.orders.index', $restaurant)
            ->with('status', 'Status pesanan diperbarui.');
    }
}
