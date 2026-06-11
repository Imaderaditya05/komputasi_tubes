<?php

namespace App\Providers;

use App\Models\CheckoutOrder;
use App\Models\CustomerNotification;
use App\Models\MitraNotification;
use App\Models\WishlistItem;
use App\Observers\CheckoutOrderObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        CheckoutOrder::observe(CheckoutOrderObserver::class);

        $cacert = config('services.midtrans.cacert_path');
        if (is_string($cacert) && $cacert !== '' && is_file($cacert)) {
            ini_set('openssl.cafile', $cacert);
            ini_set('curl.cainfo', $cacert);
        }

        Carbon::setLocale('id');

        App::setLocale('id');

        View::composer(['surprisebite.admin.*', 'components.layouts.admin'], function ($view): void {
            $u = Auth::user();
            $session = session('auth', []);
            $auth = $u ? array_merge($session, [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
            ]) : $session;
            $view->with('auth', $auth);
        });

        View::composer('components.layouts.app', function ($view): void {
            $wishlistRestaurantKeys = [];
            $wishlistMenuSlugs = [];
            $u = Auth::user();
            if ($u && $u->role === 'customer' && Schema::hasTable('wishlist_items')) {
                $items = WishlistItem::query()
                    ->where('user_id', $u->id)
                    ->get(['item_type', 'target_key']);
                foreach ($items as $item) {
                    if ($item->item_type === 'restaurant') {
                        $wishlistRestaurantKeys[] = $item->target_key;
                    } elseif ($item->item_type === 'menu') {
                        $wishlistMenuSlugs[] = $item->target_key;
                    }
                }
            }
            $view->with('wishlistRestaurantKeys', $wishlistRestaurantKeys);
            $view->with('wishlistMenuSlugs', $wishlistMenuSlugs);

            $customerUnreadNotifCount = 0;
            $uNotify = Auth::user();
            if (
                $uNotify
                && $uNotify->role === 'customer'
                && Schema::hasTable('customer_notifications')
            ) {
                $customerUnreadNotifCount = (int) CustomerNotification::query()
                    ->where('user_id', $uNotify->id)
                    ->whereNull('read_at')
                    ->count();
            }
            $view->with('customerUnreadNotifCount', $customerUnreadNotifCount);

            $mitraUnreadNotifCount = 0;
            if (
                $uNotify
                && $uNotify->role === 'mitra'
                && Schema::hasTable('mitra_notifications')
            ) {
                $mitraUnreadNotifCount = (int) MitraNotification::query()
                    ->where('user_id', $uNotify->id)
                    ->whereNull('read_at')
                    ->count();
            }
            $view->with('mitraUnreadNotifCount', $mitraUnreadNotifCount);
        });
    }
}
