<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::whereHas('store', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->with('store')->get();

        return view('dashboards.seller.products.index', compact('products'));
    }

    public function create(Request $request)
    {
        $stores = Store::where('user_id', $request->user()->id)->get();
        if ($stores->isEmpty()) {
            return redirect()->route('seller.stores.create')->with('error', 'Please create a store first.');
        }
        return view('dashboards.seller.products.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $store = Store::findOrFail($validated['store_id']);
        abort_unless($store->user_id === $request->user()->id, 403, 'Unauthorized action.');

        Product::create($validated);

        return redirect()->route('seller.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Request $request, Product $product)
    {
        abort_unless($product->store->user_id === $request->user()->id, 403, 'Unauthorized action.');
        return view('dashboards.seller.products.show', compact('product'));
    }

    public function edit(Request $request, Product $product)
    {
        abort_unless($product->store->user_id === $request->user()->id, 403, 'Unauthorized action.');
        $stores = Store::where('user_id', $request->user()->id)->get();
        return view('dashboards.seller.products.edit', compact('product', 'stores'));
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($product->store->user_id === $request->user()->id, 403, 'Unauthorized action.');

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $store = Store::findOrFail($validated['store_id']);
        abort_unless($store->user_id === $request->user()->id, 403, 'Unauthorized action.');

        $product->update($validated);

        return redirect()->route('seller.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($product->store->user_id === $request->user()->id, 403, 'Unauthorized action.');
        
        $product->delete();

        return redirect()->route('seller.products.index')->with('success', 'Product deleted successfully.');
    }
}
