<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\MitraNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MitraNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'mitra', 403);

        if (! Schema::hasTable('mitra_notifications')) {
            return view('mitra.notifications.index', ['notifications' => collect()]);
        }

        $notifications = MitraNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('mitra.notifications.index', ['notifications' => $notifications]);
    }

    /** Polling badge + daftar realtime (minimal, kompatibel dengan site-realtime). */
    public function live(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== 'mitra' || ! Schema::hasTable('mitra_notifications')) {
            return response()->json([
                'fingerprint' => '',
                'unread_count' => 0,
                'notifications' => [],
                'tracker_url_tpl' => '',
            ]);
        }

        $notifications = MitraNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(80)
            ->get([
                'id',
                'type',
                'title',
                'body',
                'public_order_id',
                'read_at',
                'created_at',
                'updated_at',
            ]);

        $unreadCount = MitraNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $maxId = (int) ($notifications->max('id'));
        $latestUpdated = optional($notifications->first())->updated_at;

        $fingerprintRaw = implode('|', [
            (string) $unreadCount,
            (string) $maxId,
            $latestUpdated?->toIso8601String() ?? '',
            (string) $notifications->count(),
        ]);

        $payload = $notifications->map(static function (MitraNotification $n): array {
            return [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'public_order_id' => $n->public_order_id,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
                'updated_at' => $n->updated_at?->toIso8601String(),
                'read' => $n->read_at !== null,
            ];
        })->values()->all();

        return response()->json([
            'fingerprint' => hash('sha256', $fingerprintRaw),
            'unread_count' => $unreadCount,
            'notifications' => $payload,
            'tracker_url_tpl' => '',
        ]);
    }

    public function markRead(Request $request, MitraNotification $notification): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'mitra', 403);

        abort_unless((int) $notification->user_id === (int) $user->id, 404);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        if ($request->expectsJson()) {
            $unread = 0;
            if (Schema::hasTable('mitra_notifications')) {
                $unread = (int) MitraNotification::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count();
            }

            return response()->json(['ok' => true, 'unread_count' => $unread]);
        }

        return redirect()->route('mitra.notifications.index')->with('status', 'Notifikasi ditandai sebagai dibaca.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'mitra', 403);

        if (Schema::hasTable('mitra_notifications')) {
            MitraNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return redirect()->route('mitra.notifications.index')->with('status', 'Semua notifikasi ditandai dibaca.');
    }
}
