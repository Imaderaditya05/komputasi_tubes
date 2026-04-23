<?php

namespace App\Http\Controllers;

use App\Models\CheckoutOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $base = CheckoutOrder::query()->where('customer_email', $user->email);

        $activeOrders = (clone $base)
            ->where(function ($q) {
                $q->whereNull('fulfillment_status')
                    ->orWhere('fulfillment_status', '!=', 'completed');
            })
            ->whereNotIn('payment_status', ['DENIED', 'CANCELED', 'EXPIRED'])
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        $historyOrders = (clone $base)
            ->where('fulfillment_status', 'completed')
            ->orderByDesc('updated_at')
            ->paginate(8);

        $money = fn (int $n): string => 'Rp '.number_format($n, 0, ',', '.');

        return view('orders.index', [
            'activeOrders' => $activeOrders,
            'historyOrders' => $historyOrders,
            'money' => $money,
            'paymentMethodLabel' => [self::class, 'formatPaymentMethod'],
            'paymentStatusLabel' => [self::class, 'formatPaymentStatus'],
            'paymentStatusClass' => [self::class, 'formatPaymentStatusClass'],
            'fulfillmentBadge' => [self::class, 'formatFulfillmentBadge'],
            'fulfillmentBadgeClass' => [self::class, 'formatFulfillmentBadgeClass'],
        ]);
    }

    public static function formatPaymentMethod(?string $m): string
    {
        return match ($m) {
            'va' => 'Midtrans (VA)',
            'cod' => 'Bayar di tempat',
            default => $m ? strtoupper($m) : '—',
        };
    }

    public static function formatPaymentStatus(?string $s): string
    {
        return match ($s) {
            'PAID' => 'Lunas',
            'PENDING' => 'Menunggu bayar',
            'PENDING_COD' => 'COD — menunggu',
            'CHALLENGE' => 'Verifikasi',
            'DENIED' => 'Ditolak',
            'EXPIRED' => 'Kedaluwarsa',
            'CANCELED' => 'Dibatalkan',
            null => 'Menunggu bayar',
            default => (string) $s,
        };
    }

    public static function formatPaymentStatusClass(?string $s): string
    {
        return match ($s) {
            'PAID' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            'PENDING', 'PENDING_COD', null => 'bg-amber-50 text-amber-900 ring-amber-200',
            'DENIED', 'EXPIRED', 'CANCELED' => 'bg-red-50 text-red-800 ring-red-200',
            default => 'bg-slate-100 text-slate-800 ring-slate-200',
        };
    }

    /** Badge utama di kartu "Pesanan aktif" (fulfillment). */
    public static function formatFulfillmentBadge(?string $paymentStatus, ?string $fulfillment): string
    {
        if (in_array($paymentStatus, ['PENDING'], true) || ($paymentStatus === null && $fulfillment === 'awaiting_payment')) {
            return 'Menunggu pembayaran';
        }

        return match ($fulfillment) {
            'awaiting_payment' => 'Menunggu pembayaran',
            'pending_confirmation' => 'Menunggu Konfirmasi',
            'received' => 'Pesanan diterima',
            'preparing' => 'Sedang disiapkan',
            'ready' => 'Siap Diambil',
            'completed' => 'Selesai',
            default => 'Diproses',
        };
    }

    public static function formatFulfillmentBadgeClass(?string $paymentStatus, ?string $fulfillment): string
    {
        if (in_array($paymentStatus, ['PENDING'], true) || $fulfillment === 'awaiting_payment') {
            return 'bg-slate-100 text-slate-700 ring-slate-200';
        }

        return match ($fulfillment) {
            'pending_confirmation' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'received' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            'preparing' => 'bg-amber-50 text-amber-900 ring-amber-200',
            'ready' => 'bg-orange-50 text-orange-800 ring-orange-200',
            'completed' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }
}
