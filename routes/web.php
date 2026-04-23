<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurpriseBiteController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\AdminLiveController;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Mitra\MitraCheckoutDeliveryController;
use App\Http\Controllers\Mitra\MitraLiveController;
use App\Http\Controllers\SiteLiveController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\WishlistController;

Route::get('/', [SurpriseBiteController::class, 'home'])->name('home');
Route::get('/browse', [SurpriseBiteController::class, 'browse'])->name('browse');
Route::get('/impact', [SurpriseBiteController::class, 'impact'])->name('impact');
Route::get('/about', [SurpriseBiteController::class, 'about'])->name('about');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::get('/register/mitra', [AuthController::class, 'showMitraRegister'])->name('register.mitra');
Route::post('/register/mitra', [AuthController::class, 'registerMitra'])->name('register.mitra.submit');
Route::get('/login/admin', [AuthController::class, 'showAdminLogin'])->name('login.admin');
Route::post('/login/admin', [AuthController::class, 'adminLogin'])->name('login.admin.submit');
Route::get('/login/seller', [AuthController::class, 'showSellerLogin'])->name('login.seller');
Route::post('/login/seller', [AuthController::class, 'sellerLogin'])->name('login.seller.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/api/live/cart', [SiteLiveController::class, 'cart'])->name('api.live.cart');
});

Route::get('/boxes/{slug}', [SurpriseBiteController::class, 'box'])->name('boxes.show');

Route::get('/api/live/catalog-hash', [SiteLiveController::class, 'catalogHash'])->name('api.live.catalog-hash');

Route::middleware('customer')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/item/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/item/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/data', [CartController::class, 'getCartData'])->name('cart.data');
    Route::post('/cart/validate-checkout', [CartController::class, 'validateForCheckout'])->name('cart.validate-checkout');

    Route::get('/orders', [OrderHistoryController::class, 'index'])->name('orders.index');
    Route::get('/orders/{publicOrderId}/track', [OrderTrackingController::class, 'show'])
        ->where('publicOrderId', '[A-Za-z0-9\-]+')
        ->name('orders.track');
    Route::post('/orders/{publicOrderId}/track/demo', [OrderTrackingController::class, 'demoAdvance'])
        ->where('publicOrderId', '[A-Za-z0-9\-]+')
        ->name('orders.track.demo');
    Route::get('/api/live/orders', [SiteLiveController::class, 'orders'])->name('api.live.orders');
    Route::get('/api/live/order/{publicOrderId}', [SiteLiveController::class, 'order'])
        ->where('publicOrderId', '[A-Za-z0-9\-]+')
        ->name('api.live.order');
    Route::get('/api/live/order/{publicOrderId}/tracking', [SiteLiveController::class, 'orderTracking'])
        ->where('publicOrderId', '[A-Za-z0-9\-]+')
        ->name('api.live.order.tracking');

    Route::get('/checkout/{slug}', [SurpriseBiteController::class, 'checkoutDelivery'])->name('checkout.delivery');
    Route::post('/checkout/{slug}/delivery', [SurpriseBiteController::class, 'checkoutDeliverySubmit'])->name('checkout.delivery.submit');
    Route::get('/checkout/{slug}/payment', [SurpriseBiteController::class, 'checkoutPayment'])->name('checkout.payment');
    Route::post('/checkout/{slug}/pay', [SurpriseBiteController::class, 'checkoutPay'])->name('checkout.pay');
    Route::get('/checkout/{slug}/success', [SurpriseBiteController::class, 'checkoutSuccess'])->name('checkout.success');

    // Xendit Payment Routes
    Route::get('/payment/checkout/{order_id}', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::get('/payment/success/{order_id}', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed/{order_id}', [PaymentController::class, 'failed'])->name('payment.failed');
    Route::get('/payment/status/{order_id}', [PaymentController::class, 'checkStatus'])->name('payment.status');
});

// Midtrans Webhook (public, tidak perlu auth)
Route::post('/webhook/midtrans', [PaymentController::class, 'webhook'])->name('webhook.midtrans');

// Redirect setelah Snap / error (tanpa middleware customer — redirect dari Midtrans)
Route::get('/payment/finish', [PaymentController::class, 'finish'])->name('payment.finish');
Route::get('/payment/error', [PaymentController::class, 'paymentError'])->name('payment.error');

Route::middleware('admin')->group(function () {
    Route::get('/admin', [SurpriseBiteController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/impact', [SurpriseBiteController::class, 'adminImpact'])->name('admin.impact');
    Route::get('/admin/transactions', [AdminPanelController::class, 'transactions'])->name('admin.transactions');
    Route::get('/admin/restaurants', [AdminPanelController::class, 'restaurants'])->name('admin.restaurants');
    Route::post('/admin/restaurants', [AdminPanelController::class, 'storeRestaurant'])->name('admin.restaurants.store');
    Route::put('/admin/restaurants/{adminRestaurant}', [AdminPanelController::class, 'updateRestaurant'])->name('admin.restaurants.update');
    Route::delete('/admin/restaurants/{adminRestaurant}', [AdminPanelController::class, 'destroyRestaurant'])->name('admin.restaurants.destroy');
    Route::get('/admin/users', [AdminPanelController::class, 'users'])->name('admin.users');
    Route::put('/admin/users/{user}', [AdminPanelController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{user}/toggle-active', [AdminPanelController::class, 'toggleUserActive'])->name('admin.users.toggle-active');
    Route::delete('/admin/users/{user}', [AdminPanelController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::get('/admin/settings', [AdminPanelController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminPanelController::class, 'saveSettings'])->name('admin.settings.save');

    Route::prefix('admin/api/live')->group(function () {
        Route::get('/dashboard', [AdminLiveController::class, 'dashboard'])->name('admin.api.live.dashboard');
        Route::get('/transactions', [AdminLiveController::class, 'transactions'])->name('admin.api.live.transactions');
        Route::get('/restaurants', [AdminLiveController::class, 'restaurants'])->name('admin.api.live.restaurants');
        Route::get('/users', [AdminLiveController::class, 'users'])->name('admin.api.live.users');
        Route::get('/settings', [AdminLiveController::class, 'settings'])->name('admin.api.live.settings');
        Route::get('/impact', [AdminLiveController::class, 'impact'])->name('admin.api.live.impact');
    });
});

// Mitra Routes
Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->group(function () {
    Route::get('/api/live/restaurant/{restaurant}', [MitraLiveController::class, 'restaurantSnapshot'])->name('mitra.api.live.restaurant');
    Route::get('/dashboard', [\App\Http\Controllers\Mitra\MitraDashboardController::class, 'index'])->name('mitra.dashboard');
    Route::post('/restaurants', [\App\Http\Controllers\Mitra\RestaurantController::class, 'store'])->name('mitra.restaurants.store');

    Route::post('/restaurants/{restaurant}/mystery-boxes', [\App\Http\Controllers\Mitra\MysteryBoxController::class, 'store'])->name('mitra.mystery-boxes.store');
    Route::put('/restaurants/{restaurant}/mystery-boxes/{menu}', [\App\Http\Controllers\Mitra\MysteryBoxController::class, 'update'])->name('mitra.mystery-boxes.update');
    Route::delete('/restaurants/{restaurant}/mystery-boxes/{menu}', [\App\Http\Controllers\Mitra\MysteryBoxController::class, 'destroy'])->name('mitra.mystery-boxes.destroy');

    Route::get('/restaurants/{restaurant}/unlock', [\App\Http\Controllers\Mitra\RestaurantAccessController::class, 'showUnlockForm'])->name('mitra.restaurants.unlock.form');
    Route::post('/restaurants/{restaurant}/unlock', [\App\Http\Controllers\Mitra\RestaurantAccessController::class, 'unlock'])->name('mitra.restaurants.unlock');
    Route::post('/restaurants/{restaurant}/lock', [\App\Http\Controllers\Mitra\RestaurantAccessController::class, 'lock'])->name('mitra.restaurants.lock');

    // Locked Routes
    Route::middleware('restaurant.unlocked')->group(function () {
        Route::get('/restaurants/{restaurant}/manage', [\App\Http\Controllers\Mitra\RestaurantController::class, 'manage'])->name('mitra.restaurants.manage');
        Route::get('/restaurants/{restaurant}/checkout-deliveries', [MitraCheckoutDeliveryController::class, 'index'])->name('mitra.checkout-deliveries');
        Route::post('/restaurants/{restaurant}/checkout-deliveries/courier', [MitraCheckoutDeliveryController::class, 'updateCourier'])->name('mitra.checkout-deliveries.courier');
        Route::resource('restaurants.menus', \App\Http\Controllers\Mitra\MenuController::class);
        Route::resource('restaurants.orders', \App\Http\Controllers\Mitra\OrderController::class)->only(['index', 'show', 'update']);
    });
});
