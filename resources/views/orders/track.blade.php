<x-layouts.app :title="'Lacak Pesanan • SurpriseBite'" variant="marketing">
@php
    $fs = $order->fulfillment_status ?? 'awaiting_payment';
    $currentStep = match ($fs) {
        'completed' => 4,
        'ready' => 3,
        'preparing', 'received' => 2,
        'pending_confirmation' => 1,
        'awaiting_payment' => 0,
        default => 1,
    };
    $steps = [
        1 => ['label' => 'Order Diterima', 'icon' => 'check'],
        2 => ['label' => 'Sedang Disiapkan', 'icon' => 'clock'],
        3 => ['label' => 'Siap Diambil', 'icon' => 'box'],
        4 => ['label' => 'Selesai', 'icon' => 'check'],
    ];
    $typeLabel = $order->fulfillment_method === 'delivery' ? 'Delivery' : 'Pickup';
@endphp

<div
    class="pb-16 pt-6 sm:pt-8"
    data-order-track-live
    data-public-order-id="{{ $order->public_order_id }}"
    data-fulfillment-status="{{ $order->fulfillment_status }}"
>
    <div class="mx-auto max-w-lg px-1">
        <a href="{{ route('orders.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-[#00a63e] hover:underline">
            <span aria-hidden="true">←</span> Kembali ke pesanan
        </a>

        @if (session('status'))
            <div class="mb-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100" role="status">
                {{ session('status') }}
            </div>
        @endif

        <h1 class="mb-8 text-3xl font-black text-[#1e2939] sm:text-4xl">Lacak pesanan <span aria-hidden="true">📦</span></h1>

        {{-- Ringkasan --}}
        <div class="mb-6 rounded-3xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#6a7282]">Order ID</p>
                    <p class="font-mono text-xl font-black text-[#00a63e]">{{ $order->public_order_id }}</p>
                </div>
                <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $fulfillmentBadgeClass($order->payment_status, $order->fulfillment_status) }}">
                    {{ $fulfillmentBadge($order->payment_status, $order->fulfillment_status) }}
                </span>
            </div>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-[#6a7282]">Restoran</dt>
                    <dd class="font-bold text-[#1e2939]">{{ $order->restaurant_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#6a7282]">Mystery box</dt>
                    <dd class="text-right font-bold text-[#1e2939]">{{ $order->box_title }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#6a7282]">Waktu ambil</dt>
                    <dd class="font-bold text-[#1e2939]">{{ $order->pickup_time ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-t border-slate-100 pt-3">
                    <dt class="text-[#6a7282]">Total</dt>
                    <dd class="text-lg font-black text-[#00a63e]">{{ $money($order->amount_idr) }}</dd>
                </div>
            </dl>
        </div>

        @if ($fs === 'awaiting_payment')
            <div class="mb-6 rounded-2xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900 ring-1 ring-amber-100">
                Menunggu pembayaran. Setelah pembayaran dikonfirmasi, status pesanan akan muncul di sini.
            </div>
        @endif

        @if ($mapPayload['showUi'])
            <section class="mb-6 rounded-3xl bg-white p-4 shadow-md ring-1 ring-slate-100 sm:p-5" aria-label="Peta">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h2 class="text-sm font-black uppercase tracking-wide text-[#6a7282]">Peta</h2>
                    <span class="text-xs font-bold text-[#00a63e]">
                        {{ $mapPayload['mode'] === 'delivery' ? 'Pengiriman' : 'Ambil di restoran' }}
                    </span>
                </div>
                <div
                    id="order-track-map"
                    class="relative h-64 w-full overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200"
                    role="img"
                    aria-label="Peta Google"
                ></div>
                <p id="order-track-map-hint" class="mt-2 text-xs text-[#6a7282]"></p>
                @if (empty(config('services.google_maps.key')))
                    <p class="mt-2 text-xs font-semibold text-amber-800">Tambahkan <code class="rounded bg-amber-100 px-1">GOOGLE_MAPS_API_KEY</code> di file .env dan aktifkan Maps JavaScript API, Directions API, serta Geocoding API di Google Cloud Console. Batasi kunci dengan referrer situs Anda.</p>
                @endif
            </section>
            <script type="application/json" id="order-track-map-data">@json($mapPayload)</script>
        @endif

        {{-- Stepper vertikal --}}
        <div class="mb-6 rounded-3xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6">
            <ol class="relative space-y-0">
                @foreach ($steps as $i => $meta)
                    @php
                        $done = $fs === 'completed' || $currentStep > $i;
                        $current = $fs !== 'completed' && $currentStep === $i;
                        $pending = $fs !== 'completed' && $currentStep < $i;
                    @endphp
                    <li class="relative flex gap-4 pb-8 last:pb-0">
                        @if ($i < 4)
                            <div class="absolute left-[18px] top-10 h-[calc(100%-0.5rem)] w-0.5 {{ $done || $current ? 'bg-[#00a63e]' : 'bg-slate-200' }}" aria-hidden="true"></div>
                        @endif
                        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 {{ $done ? 'border-[#00a63e] bg-[#00a63e] text-white' : ($current ? 'border-[#00a63e] bg-emerald-50 text-[#00a63e]' : 'border-slate-200 bg-slate-100 text-slate-400') }}">
                            @if ($meta['icon'] === 'check')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @elseif ($meta['icon'] === 'clock')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1 pt-1">
                            <div class="{{ $current ? 'rounded-2xl bg-emerald-50 px-3 py-2 ring-1 ring-emerald-100' : '' }}">
                                <p class="font-black text-[#1e2939]">{{ $meta['label'] }}</p>
                                @if ($current)
                                    <p class="mt-1 text-xs font-bold text-[#00a63e]">Status saat ini</p>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>

            @if ($demoEnabled)
                <form action="{{ route('orders.track.demo', ['publicOrderId' => $order->public_order_id]) }}" method="post" class="mt-4">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-[#00a63e] py-3.5 text-sm font-black text-white shadow-md transition hover:bg-[#008f36]">
                        <span aria-hidden="true">🚚</span>
                        Lanjut ke status berikutnya (next)
                    </button>
                </form>
            @endif
        </div>

        {{-- Alamat / waktu --}}
        <div class="mb-8 space-y-4">
            @if ($order->fulfillment_method === 'delivery' && $order->delivery_address)
                <div class="rounded-2xl bg-emerald-50/80 px-4 py-4 ring-1 ring-emerald-100">
                    <p class="mb-1 flex items-center gap-2 text-xs font-black uppercase tracking-wide text-emerald-900">
                        <span aria-hidden="true">📍</span> Alamat pengiriman
                    </p>
                    <p class="text-sm font-semibold text-[#1e2939]">{{ $order->delivery_address }}</p>
                </div>
            @endif
            <div class="rounded-2xl bg-orange-50 px-4 py-4 ring-1 ring-orange-100">
                <p class="mb-1 flex items-center gap-2 text-xs font-black uppercase tracking-wide text-orange-900">
                    <span aria-hidden="true">🕐</span> Waktu pengambilan
                </p>
                <p class="text-sm font-bold text-[#1e2939]">{{ $order->pickup_time ?: '—' }}</p>
                <p class="mt-1 text-xs text-[#6a7282]">Tipe: {{ $typeLabel }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <a href="{{ route('about') }}"
               class="flex items-center justify-center gap-2 rounded-2xl border-2 border-[#00a63e] bg-white py-3 text-sm font-black text-[#00a63e] transition hover:bg-[#f0fdf4]">
                <span aria-hidden="true">📞</span>
                Hubungi restoran
            </a>
            <a href="mailto:{{ config('mail.from.address', 'support@surprisebite.com') }}"
               class="flex items-center justify-center gap-2 rounded-2xl border-2 border-[#ff6900] bg-white py-3 text-sm font-black text-[#ff6900] transition hover:bg-orange-50">
                <span aria-hidden="true">💬</span>
                Chat support
            </a>
        </div>
    </div>
</div>
</x-layouts.app>
