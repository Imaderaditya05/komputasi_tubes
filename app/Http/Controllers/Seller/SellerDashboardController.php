<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;

class SellerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $totalStores = Store::where('user_id', $user->id)->count();
        
        $totalProducts = Product::whereHas('store', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        
        $recentOrders = Order::whereHas('product.store', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['product', 'user'])->latest()->take(5)->get();
        
        $totalRevenue = Order::whereHas('product.store', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'completed')->sum('total_price');

        return view('dashboards.seller', compact(
            'totalStores', 
            'totalProducts', 
            'recentOrders', 
            'totalRevenue'
        ));
    }
}
