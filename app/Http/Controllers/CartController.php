<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CatalogRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Katalog mystery box (admin + builtin + mitra) — selaras dengan browse.
     *
     * @return array<string, array{slug: string, title: string, restaurant_id: string, restaurant_name: string, price: int, stock: int}>
     */
    private function getCatalogData(): array
    {
        $catalog = app(CatalogRepository::class)->getCatalog();
        $map = [];
        foreach ($catalog['boxes'] as $box) {
            $slug = $box['slug'] ?? '';
            if ($slug === '') {
                continue;
            }
            $restaurantName = '';
            foreach ($catalog['restaurants'] as $r) {
                if (($r['id'] ?? '') === ($box['restaurant_id'] ?? '')) {
                    $restaurantName = (string) ($r['name'] ?? '');
                    break;
                }
            }
            $map[$slug] = [
                'slug' => $slug,
                'title' => (string) ($box['title'] ?? ''),
                'restaurant_id' => (string) ($box['restaurant_id'] ?? ''),
                'restaurant_name' => $restaurantName !== '' ? $restaurantName : 'Restoran',
                'price' => (int) ($box['price'] ?? 0),
                'stock' => (int) ($box['stock'] ?? 0),
            ];
        }

        return $map;
    }

    private function getOrCreateCart(int $userId): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId],
        );
    }

    private function cartUserIdOrRedirect(Request $request): ?int
    {
        if (! Auth::check()) {
            return null;
        }

        if (Auth::user()->role !== 'customer') {
            return null;
        }

        return (int) Auth::id();
    }

    public function index(Request $request): View|RedirectResponse
    {
        $userId = $this->cartUserIdOrRedirect($request);
        if ($userId === null) {
            if (! Auth::check()) {
                return redirect()->route('login')->with('status', 'Login sebagai pelanggan untuk melihat keranjang.');
            }

            return redirect()->route('home')->withErrors(['email' => 'Keranjang hanya untuk akun pelanggan.']);
        }

        $cart = $this->getOrCreateCart($userId);
        $cart->load('items');

        return view('cart.index', [
            'cart' => $cart,
            'items' => $cart->items,
            'totalPrice' => $cart->getTotalPrice(),
            'totalQuantity' => $cart->getTotalQuantity(),
            'isEmpty' => $cart->isEmpty(),
            'restaurants' => $cart->getRestaurants(),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $userId = $this->cartUserIdOrRedirect($request);
        if ($userId === null) {
            if (! Auth::check()) {
                return redirect()->route('login')->with('status', 'Login sebagai pelanggan untuk menambahkan ke keranjang.');
            }

            return back()->withErrors(['auth' => 'Hanya pelanggan yang dapat berbelanja.']);
        }

        $validated = $request->validate([
            'box_slug' => 'required|string',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $catalog = $this->getCatalogData();
        $boxSlug = $validated['box_slug'];
        $quantity = $validated['quantity'];

        if (! isset($catalog[$boxSlug])) {
            return back()->withErrors(['box' => 'Mystery box tidak ditemukan']);
        }

        $boxData = $catalog[$boxSlug];

        if ($quantity > $boxData['stock']) {
            return back()->withErrors(['quantity' => "Quantity melebihi stok tersedia ({$boxData['stock']})"])->withInput();
        }

        $cart = $this->getOrCreateCart($userId);

        $existingItem = $cart->items()->where('box_slug', $boxSlug)->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($newQuantity > $boxData['stock']) {
                return back()->withErrors(['quantity' => "Total quantity melebihi stok ({$boxData['stock']})"]);
            }
            $existingItem->update(['quantity' => $newQuantity]);
            Session::flash('success', "'{$boxData['title']}' berhasil diperbarui");
        } else {
            $cart->items()->create([
                'box_slug' => $boxSlug,
                'box_title' => $boxData['title'],
                'restaurant_name' => $boxData['restaurant_name'],
                'price' => $boxData['price'],
                'quantity' => $quantity,
                'stock_available' => $boxData['stock'],
            ]);
            Session::flash('success', "'{$boxData['title']}' ditambahkan ke keranjang");
        }

        return redirect()->route('cart.index');
    }

    public function update(Request $request, int $itemId): RedirectResponse
    {
        $userId = $this->cartUserIdOrRedirect($request);
        if ($userId === null) {
            return redirect()->route('login');
        }

        $cart = $this->getOrCreateCart($userId);

        $item = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->first();

        if (! $item) {
            return back()->withErrors(['item' => 'Item tidak ditemukan']);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        $newQuantity = (int) $validated['quantity'];

        if ($newQuantity === 0) {
            $itemTitle = $item->box_title;
            $item->delete();
            Session::flash('success', "'{$itemTitle}' dihapus dari keranjang");

            return redirect()->route('cart.index');
        }

        if ($newQuantity > $item->stock_available) {
            return back()->withErrors(['quantity' => "Quantity tidak boleh melebihi stok ({$item->stock_available})"]);
        }

        $item->update(['quantity' => $newQuantity]);
        Session::flash('success', 'Quantity diperbarui');

        return redirect()->route('cart.index');
    }

    public function remove(int $itemId): RedirectResponse
    {
        $request = request();
        $userId = $this->cartUserIdOrRedirect($request);
        if ($userId === null) {
            return redirect()->route('login');
        }

        $cart = $this->getOrCreateCart($userId);

        $item = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->first();

        if (! $item) {
            return back()->withErrors(['item' => 'Item tidak ditemukan']);
        }

        $itemTitle = $item->box_title;
        $item->delete();
        Session::flash('success', "'{$itemTitle}' dihapus dari keranjang");

        return redirect()->route('cart.index');
    }

    public function clear(): RedirectResponse
    {
        $request = request();
        $userId = $this->cartUserIdOrRedirect($request);
        if ($userId === null) {
            return redirect()->route('login');
        }

        $cart = $this->getOrCreateCart($userId);
        $cart->clear();

        Session::flash('success', 'Keranjang telah dikosongkan');

        return redirect()->route('cart.index');
    }

    public function getCartData(Request $request)
    {
        $userId = $this->cartUserIdOrRedirect($request);
        if ($userId === null) {
            return response()->json(['success' => false], 401);
        }

        $cart = $this->getOrCreateCart($userId);

        return response()->json([
            'success' => true,
            'itemCount' => $cart->getTotalQuantity(),
            'totalPrice' => $cart->getTotalPrice(),
        ]);
    }

    public function validateForCheckout(Request $request)
    {
        $userId = $this->cartUserIdOrRedirect($request);
        if ($userId === null) {
            return response()->json(['valid' => false, 'errors' => ['Unauthorized']], 401);
        }

        $cart = $this->getOrCreateCart($userId);
        $catalog = $this->getCatalogData();
        $errors = [];

        if ($cart->isEmpty()) {
            $errors[] = 'Keranjang kosong';
        }

        foreach ($cart->items as $item) {
            if (! isset($catalog[$item->box_slug])) {
                $errors[] = "'{$item->box_title}' tidak ada di katalog";
            } elseif ($item->quantity > $catalog[$item->box_slug]['stock']) {
                $errors[] = "'{$item->box_title}' stok tidak cukup";
            }
        }

        if (count($cart->getRestaurants()) > 1) {
            $errors[] = 'Cart memiliki item dari multiple restaurants (tidak diizinkan)';
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
        ]);
    }
}
