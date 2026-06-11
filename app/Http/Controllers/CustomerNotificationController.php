<?php

namespace App\Http\Controllers;

use App\Models\CustomerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CustomerNotificationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user() && $request->user()->role === 'customer', 403);

        if (! Schema::hasTable('customer_notifications')) {
            return view('notifications.index', ['notifications' => collect()]);
        }

        $notifications = CustomerNotification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, CustomerNotification $notification): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'customer', 403);

        abort_unless((int) $notification->user_id === (int) $user->id, 404);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        if ($request->expectsJson()) {
            $unread = 0;
            if (Schema::hasTable('customer_notifications')) {
                $unread = (int) CustomerNotification::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count();
            }

            return response()->json(['ok' => true, 'unread_count' => $unread]);
        }

        return redirect()->route('notifications.index')->with('status', 'Notifikasi ditandai sebagai dibaca.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'customer', 403);

        if (Schema::hasTable('customer_notifications')) {
            CustomerNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return redirect()->route('notifications.index')->with('status', 'Semua notifikasi ditandai dibaca.');
    }
}
