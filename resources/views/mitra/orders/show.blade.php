@php
    $phase = \App\Services\MitraCheckoutOrderStatusService::lifecyclePhase($order);
    $phaseLabel = match ($phase) {
        'completed' => 'Selesai',
        'paid' => 'Lunas / diproses',
        default => 'Menunggu pembayaran',
    };
@endphp

<x-layouts.app :title="'Pesanan ' . $order->public_order_id">
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6">
                <a href="{{ route('restaurants.orders.index', $restaurant) }}" class="text-sm font-bold text-emerald-700 hover:underline">← Daftar pesanan</a>
            </div>

            <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Checkout</p>
                    <h1 class="text-2xl font-black text-gray-900 mt-1">{{ $order->public_order_id }}</h1>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-black bg-slate-100 text-slate-800 ring-1 ring-slate-200">Status ringkas: {{ $phaseLabel }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-bold bg-white text-gray-700 ring-1 ring-gray-200">{{ strtoupper((string) ($order->payment_method ?? '—')) }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-bold bg-white text-gray-700 ring-1 ring-gray-200">{{ $order->fulfillment_method }}</span>
                    </div>
                </div>
                <dl class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-bold text-gray-500 text-xs uppercase">Menu</dt>
                        <dd class="font-semibold text-gray-900 mt-1">{{ $order->box_title ?? $order->box_slug }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-gray-500 text-xs uppercase">Pelanggan (email sistem)</dt>
                        <dd class="font-semibold text-gray-900 mt-1 break-all">{{ \App\Services\MitraRestaurantFeedbackService::maskEmail($order->customer_email) }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-gray-500 text-xs uppercase">Jumlah dibayar (IDR)</dt>
                        <dd class="font-black text-emerald-700 mt-1">Rp {{ number_format((int) $order->amount_idr, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-gray-500 text-xs uppercase">Status pembayaran</dt>
                        <dd class="font-semibold text-gray-900 mt-1">{{ $order->payment_status ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-bold text-gray-500 text-xs uppercase">Status fulfillment</dt>
                        <dd class="font-semibold text-gray-900 mt-1">{{ str_replace('_', ' ', $order->fulfillment_status ?? '—') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
                <h2 class="text-lg font-black text-gray-900">Ubah status (mitra)</h2>
                <p class="text-sm text-gray-600 mt-2">
                    <strong>Menunggu pembayaran</strong> — utama untuk pembayaran online yang belum selesai atau mengembalikan COD ke «belum lunas» hanya jika stok sistem belum terlanjut dicatat.
                    <strong>Lunas</strong> — untuk COD: tandai setelah pembayaran tunai diterima. Online: pembayaran otomatis lewat gateway.
                    <strong>Selesai</strong> — pesanan ditutup untuk pelanggan (pickup akan otomatis tervalidasi oleh mitra).
                </p>

                <form action="{{ route('restaurants.orders.status', [$restaurant, $order->public_order_id]) }}" method="post" class="mt-6 flex flex-wrap items-end gap-4">
                    @csrf
                    <div class="flex-1 min-w-[14rem]">
                        <label for="phase" class="block text-xs font-black uppercase tracking-wider text-gray-600 mb-1">Target status</label>
                        <select name="phase" id="phase" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm font-semibold">
                            <option value="pending" @selected($phase === 'pending')>Menunggu pembayaran</option>
                            <option value="paid" @selected($phase === 'paid')>Lunas / diproses</option>
                            <option value="completed" @selected($phase === 'completed')>Selesai</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex justify-center rounded-xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow hover:bg-emerald-700 transition">
                        Simpan status
                    </button>
                </form>
                <p class="mt-6 text-xs text-gray-500">Detail alur tingkat lanjutan (kirim kurir, validasi QR pickup) tetap di menu «Lacak delivery» atau «Validasi pickup» untuk pesanan tersebut.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
