<?php

namespace App\Observers;

use App\Models\CheckoutOrder;
use App\Models\CustomerNotification;
use App\Models\MitraNotification;
use Illuminate\Support\Facades\Schema;

class CheckoutOrderObserver
{
    private function fulfillmentLabel(?string $fs): ?string
    {
        return match ($fs) {
            'pending_confirmation' => 'Menunggu konfirmasi restoran',
            'received' => 'Pesanan diterima restoran',
            'preparing' => 'Sedang disiapkan',
            'ready' => 'Siap diambil / siap kirim',
            'completed' => 'Pesanan selesai',
            'awaiting_payment' => null,
            default => $fs ? 'Status: '.$fs : null,
        };
    }

    public function created(CheckoutOrder $order): void
    {
        CustomerNotification::recordForCustomer(
            $order->customer_email,
            'order_created',
            $order->public_order_id,
            'Pesanan Berhasil Dibuat',
            sprintf(
                'Pesanan %s untuk "%s" di %s sudah dibuat. %s',
                $order->public_order_id,
                $order->box_title ?? 'menu',
                $order->restaurant_name ?? 'restoran',
                in_array((string) $order->payment_status, ['PENDING', 'PENDING_COD'], true)
                    ? 'Selesaikan pembayaran agar pesanan diproses.'
                    : ''
            ),
        );

        if (Schema::hasTable('mitra_notifications')) {
            $ownerId = MitraNotification::mitraOwnerUserIdForCheckoutOrder($order);
            MitraNotification::recordForOrder(
                $ownerId,
                'mitra_order_created',
                $order->public_order_id,
                'Pesanan checkout baru',
                sprintf(
                    '%s untuk "%s" — %s. Metode: %s.',
                    $order->public_order_id,
                    $order->box_title ?? 'menu',
                    $order->restaurant_name ?? 'restoran',
                    strtoupper((string) ($order->payment_method ?? '—')),
                ),
            );
        }
    }

    public function updated(CheckoutOrder $order): void
    {
        $email = $order->customer_email;

        if ($order->wasChanged('payment_status') && $order->payment_status === 'PAID') {
            CustomerNotification::recordForCustomer(
                $email,
                'payment_paid',
                $order->public_order_id,
                'Pembayaran Berhasil',
                sprintf(
                    'Pembayaran untuk %s sudah dikonfirmasi. Restoran akan memproses pesanan Anda.',
                    $order->public_order_id,
                ),
            );

            if (Schema::hasTable('mitra_notifications')) {
                $ownerId = MitraNotification::mitraOwnerUserIdForCheckoutOrder($order);
                MitraNotification::recordForOrder(
                    $ownerId,
                    'mitra_payment_paid',
                    $order->public_order_id,
                    'Pembayaran lunas',
                    sprintf('Pesanan %s sudah lunas dan siap diproses.', $order->public_order_id),
                );
            }
        }

        if ($order->wasChanged('fulfillment_status') && ($order->fulfillment_status ?? '') === 'completed') {
            if (Schema::hasTable('mitra_notifications')) {
                $ownerId = MitraNotification::mitraOwnerUserIdForCheckoutOrder($order);
                MitraNotification::recordForOrder(
                    $ownerId,
                    'mitra_order_completed',
                    $order->public_order_id,
                    'Pesanan selesai',
                    sprintf('Pesanan %s ditandai selesai di sistem.', $order->public_order_id),
                );
            }
        }

        if ($order->wasChanged('fulfillment_status')) {
            $fs = $order->fulfillment_status;
            $label = $this->fulfillmentLabel($fs);
            if ($label !== null && $label !== '') {
                $suffix = strtolower(preg_replace('/[^a-z0-9\-_]+/', '_', (string) $fs));
                $suffix = trim($suffix, '_');
                $typeKey = 'fulfillment_'.substr($suffix !== '' ? $suffix : md5((string) $fs), 0, 48);

                CustomerNotification::recordForCustomer(
                    $email,
                    $typeKey,
                    $order->public_order_id,
                    'Status pesanan diperbarui',
                    sprintf('%s (%s).', $label, $order->public_order_id),
                );
            }
        }
    }
}
