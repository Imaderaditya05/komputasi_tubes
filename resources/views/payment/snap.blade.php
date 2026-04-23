<x-layouts.app :title="'Bayar • SurpriseBite'" variant="marketing">
    <div class="mx-auto max-w-lg px-4 py-16 text-center">
        <p class="text-lg font-black text-[#1e2939]">Membuka pembayaran Midtrans…</p>
        <p class="mt-2 text-sm text-[#6a7282]">Jika jendela bayar tidak muncul, ketuk tombol di bawah.</p>
        <button type="button" id="midtrans-pay"
                class="mt-6 rounded-full bg-[#00a63e] px-8 py-3 text-sm font-black text-white shadow-md shadow-emerald-900/20 transition hover:bg-[#008f3a]">
            Buka pembayaran
        </button>
        <a href="{{ route('checkout.payment', ['slug' => $order->box_slug]) }}"
           class="mt-6 block text-sm font-bold text-[#6a7282] underline-offset-2 hover:underline">
            Batal — kembali ke checkout
        </a>
    </div>

    <script src="{{ $snapJsHost }}/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    <script>
        (function () {
            var token = @json($snapToken);
            var orderId = @json($order->public_order_id);
            var backUrl = @json(route('checkout.payment', ['slug' => $order->box_slug]));
            var finishUrl = @json(route('payment.finish'));
            var errorUrl = @json(route('payment.error'));

            function pay() {
                if (typeof snap === 'undefined') {
                    return;
                }
                snap.pay(token, {
                    onSuccess: function (result) {
                        var oid = (result && result.order_id) ? result.order_id : orderId;
                        window.location.href = finishUrl + '?order_id=' + encodeURIComponent(oid)
                            + '&status_code=200&transaction_status=settlement';
                    },
                    onPending: function (result) {
                        var oid = (result && result.order_id) ? result.order_id : orderId;
                        window.location.href = finishUrl + '?order_id=' + encodeURIComponent(oid);
                    },
                    onError: function () {
                        window.location.href = errorUrl + '?order_id=' + encodeURIComponent(orderId);
                    },
                    onClose: function () {
                        window.location.href = backUrl;
                    }
                });
            }

            document.getElementById('midtrans-pay').addEventListener('click', pay);
            window.addEventListener('load', pay);
        })();
    </script>
</x-layouts.app>
