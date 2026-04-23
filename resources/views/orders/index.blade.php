<x-layouts.app :title="'Pesanan Saya • SurpriseBite'" variant="marketing">
@php
    $hasAnyOrder = $activeOrders->isNotEmpty() || $historyOrders->isNotEmpty();
@endphp
<div class="pb-16 pt-6 sm:pt-8" data-orders-live>
    <div class="mx-auto max-w-3xl">
        @if (session('status'))
            <div class="mb-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100" role="status">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-8">
            <h1 class="flex items-center gap-3 text-3xl font-black text-[#1e2939] sm:text-4xl">
                <svg class="h-8 w-8 shrink-0 text-[#ff6900] sm:h-10 sm:w-10" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Pesanan Saya
            </h1>
            <p class="mt-2 text-base text-[#6a7282] sm:text-lg">Pesanan aktif dan riwayat transaksi — status diperbarui otomatis.</p>
        </div>

        @if (! $hasAnyOrder)
            <div class="rounded-3xl bg-white px-8 py-16 text-center shadow-md ring-1 ring-slate-100">
                <div class="mb-4 text-6xl" aria-hidden="true">📋</div>
                <h2 class="mb-3 text-2xl font-black text-[#1e2939]">Belum ada pesanan</h2>
                <p class="mb-8 text-[#6a7282]">Transaksi Anda akan muncul di sini setelah checkout.</p>
                <a href="{{ route('browse') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#ff6900] to-[#f54900] px-8 py-3 text-base font-bold text-white shadow-lg transition hover:shadow-xl">
                    Jelajahi Mystery Boxes
                </a>
            </div>
        @else
            {{-- Pesanan aktif --}}
            <section class="mb-12">
                <h2 class="mb-4 text-lg font-black text-[#1e2939]">Pesanan aktif</h2>
                @if ($activeOrders->isEmpty())
                    <p class="rounded-2xl bg-slate-50 px-5 py-6 text-sm text-[#6a7282] ring-1 ring-slate-100">Tidak ada pesanan yang sedang diproses.</p>
                @else
                    <div class="space-y-5">
                        @foreach ($activeOrders as $order)
                            <article
                                class="rounded-3xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6"
                                data-order-row="{{ $order->public_order_id }}"
                                data-payment-status="{{ $order->payment_status }}"
                                data-fulfillment-status="{{ $order->fulfillment_status }}"
                            >
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-[#6a7282]">Order ID</p>
                                        <p class="font-mono text-lg font-black text-[#00a63e]">{{ $order->public_order_id }}</p>
                                    </div>
                                    <span
                                        data-order-fulfillment-badge
                                        class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $fulfillmentBadgeClass($order->payment_status, $order->fulfillment_status) }}"
                                    >
                                        {{ $fulfillmentBadge($order->payment_status, $order->fulfillment_status) }}
                                    </span>
                                </div>
                                <h3 class="text-xl font-black text-[#1e2939]">{{ $order->box_title }}</h3>
                                <p class="mt-1 text-sm font-medium text-[#6a7282]">{{ $order->restaurant_name }}</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-900 ring-1 ring-emerald-100">
                                        {{ $order->pickup_time ?: '—' }}
                                    </span>
                                    <span class="inline-flex rounded-xl bg-orange-50 px-3 py-2 text-xs font-bold text-orange-900 ring-1 ring-orange-100">
                                        {{ $order->fulfillment_method === 'delivery' ? 'Delivery' : 'Pickup' }}
                                    </span>
                                    <span class="inline-flex rounded-xl bg-emerald-50/80 px-3 py-2 text-xs font-black text-[#00a63e] ring-1 ring-emerald-100">
                                        {{ $money($order->amount_idr) }}
                                    </span>
                                </div>
                                <a href="{{ route('orders.track', ['publicOrderId' => $order->public_order_id]) }}"
                                   class="mt-5 flex w-full items-center justify-center gap-2 rounded-2xl bg-[#00a63e] py-3.5 text-sm font-black text-white shadow-md transition hover:bg-[#008f36]">
                                    Lacak pesanan
                                    <span aria-hidden="true">→</span>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Riwayat --}}
            <section>
                <h2 class="mb-4 text-lg font-black text-[#1e2939]">Riwayat pesanan</h2>
                @if ($historyOrders->isEmpty())
                    <p class="rounded-2xl bg-slate-50 px-5 py-6 text-sm text-[#6a7282] ring-1 ring-slate-100">Belum ada pesanan selesai.</p>
                @else
                    <div class="space-y-5">
                        @foreach ($historyOrders as $order)
                            <article class="rounded-3xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-[#6a7282]">Order ID</p>
                                        <p class="font-mono text-lg font-black text-[#00a63e]">{{ $order->public_order_id }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($order->reviewed)
                                            <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-900 ring-1 ring-amber-200">Reviewed</span>
                                        @endif
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-800 ring-1 ring-emerald-200">Selesai</span>
                                    </div>
                                </div>
                                <h3 class="text-xl font-black text-[#1e2939]">{{ $order->box_title }}</h3>
                                <p class="mt-1 text-sm font-medium text-[#6a7282]">{{ $order->restaurant_name }}</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-xl bg-slate-50 px-3 py-2 text-xs font-bold text-[#364153] ring-1 ring-slate-100">
                                        {{ $order->created_at->translatedFormat('Y-m-d') }}
                                    </span>
                                    <span class="inline-flex rounded-xl bg-orange-50 px-3 py-2 text-xs font-bold text-orange-900 ring-1 ring-orange-100">
                                        {{ $paymentMethodLabel($order->payment_method) }}
                                    </span>
                                    <span class="inline-flex rounded-xl bg-emerald-50/80 px-3 py-2 text-xs font-black text-[#00a63e] ring-1 ring-emerald-100">
                                        {{ $money($order->amount_idr) }}
                                    </span>
                                </div>
                                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <a href="{{ route('orders.track', ['publicOrderId' => $order->public_order_id]) }}"
                                       class="flex items-center justify-center rounded-2xl border-2 border-[#00a63e] bg-white py-3 text-sm font-black text-[#00a63e] transition hover:bg-[#f0fdf4]">
                                        Lihat detail
                                    </a>
                                    <a href="{{ route('boxes.show', ['slug' => $order->box_slug]) }}"
                                       class="flex items-center justify-center rounded-2xl bg-[#00a63e] py-3 text-sm font-black text-white shadow-md transition hover:bg-[#008f36]">
                                        Pesan lagi
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-8">
                        {{ $historyOrders->links() }}
                    </div>
                @endif
            </section>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('browse') }}"
                   class="inline-flex items-center justify-center rounded-full border-2 border-[#00a63e] bg-white px-6 py-3 text-sm font-black text-[#00a63e] shadow-sm transition hover:bg-[#f0fdf4]">
                    Lanjut belanja
                </a>
                <a href="{{ route('cart.index') }}"
                   class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6900] to-[#f54900] px-6 py-3 text-sm font-bold text-white shadow-md transition hover:shadow-lg">
                    Lihat keranjang
                </a>
            </div>
        @endif
    </div>
</div>
</x-layouts.app>
