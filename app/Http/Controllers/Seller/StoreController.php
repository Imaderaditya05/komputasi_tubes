<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::where('user_id', $request->user()->id)->get();
        return view('dashboards.seller.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('dashboards.seller.stores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $request->user()->stores()->create($validated);

        return redirect()->route('seller.stores.index')->with('success', 'Store created successfully.');
    }

    public function show(Request $request, Store $store)
    {
        abort_unless($store->user_id === $request->user()->id, 403, 'Unauthorized action.');
        return view('dashboards.seller.stores.show', compact('store'));
    }

    public function edit(Request $request, Store $store)
    {
        abort_unless($store->user_id === $request->user()->id, 403, 'Unauthorized action.');
        return view('dashboards.seller.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        abort_unless($store->user_id === $request->user()->id, 403, 'Unauthorized action.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $store->update($validated);

        return redirect()->route('seller.stores.index')->with('success', 'Store updated successfully.');
    }

    public function destroy(Request $request, Store $store)
    {
        abort_unless($store->user_id === $request->user()->id, 403, 'Unauthorized action.');
        
        $store->delete();

        return redirect()->route('seller.stores.index')->with('success', 'Store deleted successfully.');
    }
}
