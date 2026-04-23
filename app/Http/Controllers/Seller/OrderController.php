<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::whereHas('product.store', function($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->with(['product', 'user'])->latest()->get();

        return view('dashboards.seller.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->product->store->user_id === $request->user()->id, 403, 'Unauthorized action.');
        
        $order->load(['product', 'user']);
        return view('dashboards.seller.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        abort_unless($order->product->store->user_id === $request->user()->id, 403, 'Unauthorized action.');

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $order->update($validated);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}
