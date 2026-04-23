<?php

namespace App\Providers;

use App\Models\WishlistItem;
use Carbon\Carbon;
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
        $cacert = config('services.midtrans.cacert_path');
        if (is_string($cacert) && $cacert !== '' && is_file($cacert)) {
            ini_set('openssl.cafile', $cacert);
            ini_set('curl.cainfo', $cacert);
        }

        Carbon::setLocale('id');

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
        });
    }
}
