<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
use App\Models\Restaurant;
use App\Services\MitraRestaurantFeedbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function store(StoreRestaurantRequest $request): RedirectResponse
    {
        Restaurant::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'pin' => Hash::make($request->pin),
            'access_status' => 'pending',
        ]);

        return redirect()->route('mitra.dashboard')->with('status', 'Restoran berhasil dibuat.');
    }

    public function manage(Request $request, Restaurant $restaurant, MitraRestaurantFeedbackService $feedback): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403, 'Unauthorized');

        $menusCount = $restaurant->menus()->count();
        $ordersCount = $feedback->checkoutOrdersCount($restaurant);
        $mitraLiveHash = MitraLiveController::fingerprintForRestaurant($restaurant);

        $feedbackSummary = $feedback->aggregateRatingSummary($restaurant);
        $pendingReportsCount = $feedback->pendingReportsCount($restaurant);
        $recentReviews = $feedback->recentOrderReviews($restaurant);
        $customerReports = $feedback->reports($restaurant);
        /** @var array<int|string, string> $menuNamesById */
        $menuNamesById = $restaurant->menus()->pluck('name', 'id')->all();
        $perMenuRatings = $restaurant->menus()->orderBy('name')->get(['id', 'name', 'avg_rating', 'ratings_count']);

        return view('mitra.restaurants.manage', compact(
            'restaurant',
            'menusCount',
            'ordersCount',
            'mitraLiveHash',
            'feedbackSummary',
            'pendingReportsCount',
            'recentReviews',
            'customerReports',
            'menuNamesById',
            'perMenuRatings',
        ));
    }
}
