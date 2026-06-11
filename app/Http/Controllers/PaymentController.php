<?php

namespace App\Http\Controllers;

use App\Models\CheckoutOrder;
use App\Services\CartAfterPaymentService;
use App\Services\MenuStockService;
use App\Services\MidtransSslConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->applyMidtransConfig();
    }

    private function applyMidtransConfig(): void
    {
        Config::$serverKey = (string) config('services.midtrans.server_key');
        Config::$clientKey = (string) config('services.midtrans.client_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        MidtransSslConfig::applyCurlCaBundle();
    }

    /**
     * Terapkan status dari API Midtrans ke order (Snap redirect / polling).
     */
    private function syncOrderStatusFromMidtrans(CheckoutOrder $order): void
    {
        if (config('services.midtrans.server_key') === '') {
            return;
        }

        $this->applyMidtransConfig();

        try {
            $status = Transaction::status($order->public_order_id);
            $transaction = $status->transaction_status ?? null;
            $type = $status->payment_type ?? null;
            $fraud = $status->fraud_status ?? null;
            if ($transaction) {
                $this->applyTransactionStatusToOrder($order, (string) $transaction, $type ? (string) $type : null, $fraud ? (string) $fraud : null);
            }
        } catch (\Throwable $e) {
            \Log::warning('Midtrans status sync failed', [
                'order' => $order->public_order_id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function applyTransactionStatusToOrder(
        CheckoutOrder $order,
        string $transaction,
        ?string $type,
        ?string $fraud
    ): void {
        $newStatus = $order->payment_status;

        if ($transaction === 'capture') {
            if ($type === 'credit_card') {
                $newStatus = $fraud === 'challenge' ? 'CHALLENGE' : 'PAID';
            }
        } elseif ($transaction === 'settlement') {
            $newStatus = 'PAID';
        } elseif ($transaction === 'pending') {
            $newStatus = 'PENDING';
        } elseif ($transaction === 'deny') {
            $newStatus = 'DENIED';
        } elseif ($transaction === 'expire') {
            $newStatus = 'EXPIRED';
        } elseif ($transaction === 'cancel') {
            $newStatus = 'CANCELED';
        }

        if ($newStatus !== $order->payment_status) {
            $data = ['payment_status' => $newStatus];
            if ($newStatus === 'PAID' && in_array($order->fulfillment_status, [null, 'awaiting_payment'], true)) {
                $data['fulfillment_status'] = 'pending_confirmation';
            }
            $order->update($data);
        } elseif ($newStatus === 'PAID' && in_array($order->fulfillment_status, [null, 'awaiting_payment'], true)) {
            $order->update(['fulfillment_status' => 'pending_confirmation']);
        }
    }

    /**
     * Redirect ke Midtrans Snap untuk pembayaran
     */
    public function checkout($order_id)
    {
        $order = CheckoutOrder::where('public_order_id', $order_id)->firstOrFail();

        if (config('services.midtrans.server_key') === '' || config('services.midtrans.client_key') === '') {
            return back()->with(
                'error',
                'Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di .env '
                .'(sandbox: aktifkan Sandbox di dashboard, salin SB-Mid-server-* dan SB-Mid-client-*), lalu php artisan config:clear.'
            );
        }

        // Jika payment URL sudah ada, redirect langsung (biar tidak double create)
        if ($order->payment_redirect_url) {
            return redirect($order->payment_redirect_url);
        }

        try {
            $qty = max(1, (int) ($order->item_quantity ?? 1));
            $gross = (int) $order->amount_idr;

            $params = [
                'transaction_details' => [
                    'order_id' => $order->public_order_id,
                    'gross_amount' => $gross,
                ],
                'customer_details' => [
                    'first_name' => explode(' ', $order->customer?->name ?? 'Customer')[0],
                    'email' => $order->customer_email,
                ],
                'item_details' => [
                    [
                        'id' => $order->box_slug,
                        'price' => $gross,
                        'quantity' => 1,
                        'name' => mb_substr($order->box_title.' ×'.$qty, 0, 50),
                    ],
                ],
                'callbacks' => [
                    'finish' => url('/payment/finish'),
                ],
            ];

            // Buat transaksi Midtrans
            $transaction = Snap::createTransaction($params);

            // Simpan snap token dan payment url di kolom yang sudah ada
            $order->update([
                'midtrans_transaction_id' => $transaction->token,
                'payment_redirect_url' => $transaction->redirect_url,
                'payment_status' => 'PENDING',
            ]);

            // Redirect ke halaman pembayaran Midtrans Snap
            return redirect($transaction->redirect_url);
        } catch (\Exception $e) {
            \Log::error('Midtrans checkout error: '.$e->getMessage(), ['exception' => $e]);
            $msg = $e->getMessage();
            if (str_contains($msg, '401') || str_contains(strtolower($msg), 'unauthorized')) {
                $sandbox = ! config('services.midtrans.is_production', false);
                $msg .= $sandbox
                    ? ' — Sandbox: di Dashboard aktifkan Sandbox, lalu salin kunci SB-Mid-server-* dan SB-Mid-client-* (bukan Mid-*). php artisan config:clear'
                    : ' — Production: salin Mid-server-* & Mid-client-* dari mode Production. php artisan config:clear';
            }

            return back()->with('error', 'Gagal membuat transaksi: '.$msg);
        }
    }

    /**
     * Redirect jika Snap onError (pembayaran gagal).
     */
    public function paymentError(Request $request): RedirectResponse
    {
        $orderId = $request->query('order_id');
        if (is_string($orderId) && $orderId !== '') {
            $order = CheckoutOrder::where('public_order_id', $orderId)->first();
            if ($order) {
                return redirect()
                    ->route('checkout.payment', ['slug' => $order->box_slug])
                    ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
            }
        }

        return redirect()->route('cart.index')->with('error', 'Pembayaran gagal.');
    }

    /**
     * Redirect setelah bayar di Snap (Midtrans menambahkan ?order_id=...&status_code=...).
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->query('order_id');
        if (! is_string($orderId) || $orderId === '') {
            return redirect()->route('home')->with('status', 'Pembayaran selesai. Cek riwayat pesanan.');
        }

        $order = CheckoutOrder::where('public_order_id', $orderId)->first();
        if (! $order) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan.');
        }

        $this->syncOrderStatusFromMidtrans($order);
        $order->refresh();

        if ($order->payment_status === 'PAID') {
            MenuStockService::applyForOrder($order);
            CartAfterPaymentService::clearForOrder($order);
        }

        return redirect()
            ->route('checkout.success', ['slug' => $order->box_slug])
            ->with('status', 'Terima kasih — status pembayaran telah disinkronkan.');
    }

    /**
     * Callback Webhook dari Midtrans — pakai body JSON dari Request (bukan php://input mentah).
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $raw = $request->getContent();
            $payload = $request->json()->all();
            if ($payload === [] && $raw !== '') {
                $decoded = json_decode($raw, true);
                $payload = is_array($decoded) ? $decoded : [];
            }
            if ($payload === []) {
                $payload = $request->all();
            }

            $order_id = $payload['order_id'] ?? null;
            $transaction = $payload['transaction_status'] ?? null;
            $type = $payload['payment_type'] ?? null;
            $fraud = $payload['fraud_status'] ?? null;

            \Log::info('Midtrans webhook received', $payload);

            if (! is_string($order_id) || $order_id === '' || ! is_string($transaction)) {
                return response()->json(['error' => 'Missing order_id or transaction_status'], 400);
            }

            $order = CheckoutOrder::where('public_order_id', $order_id)->first();
            if (! $order) {
                \Log::warning('Order not found for order_id: '.$order_id);

                return response()->json(['error' => 'Order not found'], 404);
            }

            $this->applyTransactionStatusToOrder(
                $order,
                $transaction,
                is_string($type) ? $type : null,
                is_string($fraud) ? $fraud : null
            );
            $order->refresh();

            if ($order->payment_status === 'PAID') {
                MenuStockService::applyForOrder($order);
                CartAfterPaymentService::clearForOrder($order);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            \Log::error('Webhook error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Midtrans Success page (redirect from Snap based on dashboard settings)
     */
    public function success($order_id)
    {
        $order = CheckoutOrder::where('public_order_id', $order_id)->firstOrFail();
        $this->syncOrderStatusFromMidtrans($order);
        $order->refresh();

        if ($order->payment_status === 'PAID') {
            MenuStockService::applyForOrder($order);
            CartAfterPaymentService::clearForOrder($order);
        }

        return view('payment.success', ['order' => $order]);
    }

    /**
     * Midtrans Failed/Error page (redirect from Snap)
     */
    public function failed($order_id)
    {
        $order = CheckoutOrder::where('public_order_id', $order_id)->firstOrFail();

        return view('payment.failed', ['order' => $order]);
    }

    /**
     * API status polling if needed
     */
    public function checkStatus($order_id)
    {
        $order = CheckoutOrder::where('public_order_id', $order_id)->firstOrFail();
        $this->syncOrderStatusFromMidtrans($order);
        $order->refresh();

        if ($order->payment_status === 'PAID') {
            MenuStockService::applyForOrder($order);
        }

        return response()->json([
            'status' => $order->payment_status,
            'order' => $order,
        ]);
    }
}
