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
    $pickupCustMsg = '';
    $pvcRemainSecs = null;
    if ($order->fulfillment_method === 'pickup' && ! in_array($fs, ['awaiting_payment'], true)) {
        $pickupCustMsg = \App\Http\Controllers\OrderHistoryController::formatFulfillmentBadge(
            $order->payment_status,
            $order->fulfillment_status,
            $order->pickup_validation_status,
            $order->fulfillment_method,
        );
        if (
            ($order->pickup_validation_status ?? '') === \App\Models\CheckoutOrder::PICKUP_VALIDATION_PENDING
            && $order->fulfillmentAllowsPickupValidation()
        ) {
            $pvcRemainSecs = app(\App\Services\PickupValidationService::class)->remainingSeconds(
                $order->pickup_validation_deadline_at,
            );
        }
    }
@endphp

<div
    class="pb-16 pt-6 sm:pt-8"
    data-order-track-live
    data-order-track-page
    data-public-order-id="{{ $order->public_order_id }}"
    data-fulfillment-status="{{ $order->fulfillment_status }}"
    data-demo-enabled="{{ $demoEnabled ? '1' : '0' }}"
    data-demo-auto="{{ $demoAuto ? '1' : '0' }}"
    data-demo-advance-url="{{ $demoEnabled ? route('orders.track.demo', ['publicOrderId' => $order->public_order_id]) : '' }}"
    data-courier-chat-url="{{ route('orders.courier-chat', ['publicOrderId' => $order->public_order_id]) }}"
    data-restaurant-chat-url="{{ route('orders.restaurant-chat', ['publicOrderId' => $order->public_order_id]) }}"
    data-fulfillment-method="{{ $order->fulfillment_method }}"
    data-pickup-validation-status="{{ $order->pickup_validation_status ?? '' }}"
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

        @if (session('partner_report_status'))
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-900 ring-1 ring-rose-100" role="status">
                {{ session('partner_report_status') }}
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
                <span
                    data-order-top-fulfillment-badge
                    class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $fulfillmentBadgeClass($order->payment_status, $order->fulfillment_status, $order->pickup_validation_status, $order->fulfillment_method) }}"
                >
                    {{ $fulfillmentBadge($order->payment_status, $order->fulfillment_status, $order->pickup_validation_status, $order->fulfillment_method) }}
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
                    <dt class="text-[#6a7282]">Jumlah</dt>
                    <dd class="font-bold text-[#1e2939]">{{ max(1, (int) ($order->item_quantity ?? 1)) }} item</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#6a7282]">Waktu checkout</dt>
                    <dd class="text-right font-bold text-[#1e2939]">{{ $order->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</dd>
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

        @if ($order->fulfillment_method === 'pickup' && ! in_array($fs, ['awaiting_payment'], true))
            <section
                id="customer-pickup-validation-section"
                class="mb-6 rounded-3xl bg-white p-5 shadow-md ring-1 ring-amber-100 sm:p-6"
                aria-label="Validasi pickup"
            >
                <div class="mb-2 flex items-center justify-between gap-3">
                    <h2 class="text-sm font-black uppercase tracking-wide text-amber-900">Validasi pickup (mitra)</h2>
                </div>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#6a7282]">Ringkasan</dt>
                        <dd id="customer-pv-msg" class="text-right font-bold text-[#1e2939]">
                            {{ $pickupCustMsg !== '' ? $pickupCustMsg : '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-amber-100 pt-3">
                        <dt class="text-[#6a7282]">Waktu tersisa (server)</dt>
                        <dd
                            id="customer-pv-remain"
                            class="font-black text-orange-700"
                            data-initial-pickup-seconds="{{ $pvcRemainSecs !== null ? $pvcRemainSecs : '' }}"
                        >
                            —
                        </dd>
                    </div>
                </dl>
                <p id="customer-pv-hint" class="mt-3 text-xs font-semibold text-[#6a7282]">
                    Pesanan Anda tetap realtime lewat pembaruan status di halaman ini.
                </p>
            </section>
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

            @if ($demoEnabled && $fs !== 'completed')
                <form action="{{ route('orders.track.demo', ['publicOrderId' => $order->public_order_id]) }}" method="post" class="mt-4">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-[#00a63e] py-3.5 text-sm font-black text-white shadow-md transition hover:bg-[#008f36]">
                        <span aria-hidden="true">🚚</span>
                        Sedang Diproses
                    </button>
                </form>
                @if ($demoAuto)
                    <p class="mt-2 text-center text-xs font-semibold text-[#6a7282]">Demo: status juga maju otomatis setiap 25 detik hingga Selesai.</p>
                @endif
            @endif
        </div>

        @if ($fs === 'completed')
            @if (! $order->reviewed)
                <div class="mb-6 rounded-3xl bg-white p-5 shadow-md ring-1 ring-amber-100 sm:p-6" data-order-rating-form>
                    <h2 class="text-lg font-black text-[#1e2939]">Nilai pesanan ini</h2>
                    <p class="mt-1 text-sm font-semibold text-[#6a7282]">Bagaimana pengalaman Anda dengan pesanan {{ $order->public_order_id }}?</p>

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900">
                            <ul class="list-inside list-disc">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post" action="{{ route('orders.review', ['publicOrderId' => $order->public_order_id]) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-slate-600">Rating</p>
                            <input type="hidden" name="rating" id="order-rating-input" value="{{ old('rating', '') }}" required>
                            <div class="mt-2 flex flex-wrap gap-1" role="group" aria-label="Bintang penilaian">
                                @for ($s = 1; $s <= 5; $s++)
                                    <button type="button" data-order-rating-star="{{ $s }}"
                                            class="order-rating-star rounded-lg px-1.5 py-0.5 text-3xl leading-none text-slate-300 transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-amber-400"
                                            aria-label="{{ $s }} dari 5 bintang">
                                        ★
                                    </button>
                                @endfor
                            </div>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Ketuk bintang 1–5</p>
                        </div>
                        <div>
                            <label for="order-review-comment" class="text-xs font-black uppercase tracking-wide text-slate-600">Komentar (opsional)</label>
                            <textarea id="order-review-comment" name="comment" rows="3" maxlength="500"
                                      class="mt-1.5 w-full rounded-2xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold text-[#1e2939] placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
                                      placeholder="Ceritakan singkat pengalaman Anda…">{{ old('comment') }}</textarea>
                        </div>
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 py-3.5 text-sm font-black text-white shadow-md transition hover:opacity-95">
                            Kirim penilaian
                        </button>
                    </form>
                </div>
            @else
                <div class="mb-6 rounded-3xl bg-gradient-to-br from-amber-50 to-orange-50/80 p-5 shadow-md ring-1 ring-amber-100 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black text-[#1e2939]">Terima kasih!</h2>
                            <p class="mt-1 text-sm font-semibold text-[#6a7282]">Penilaian Anda telah kami simpan.</p>
                        </div>
                        <div class="text-2xl font-black text-amber-500" aria-hidden="true">
                            @for ($s = 1; $s <= 5; $s++)
                                <span class="{{ $s <= (int) ($order->customer_rating ?? 0) ? 'text-amber-500' : 'text-slate-300' }}">★</span>
                            @endfor
                        </div>
                    </div>
                    @if ($order->customer_review_comment)
                        <blockquote class="mt-4 rounded-2xl border border-amber-100/80 bg-white/80 px-4 py-3 text-sm font-semibold text-[#364153]">
                            {{ $order->customer_review_comment }}
                        </blockquote>
                    @endif
                </div>
            @endif

            <section
                id="order-partner-report"
                class="mb-6 max-w-2xl rounded-2xl border-2 border-rose-100 bg-gradient-to-br from-rose-50/90 to-white p-5 shadow-sm ring-1 ring-rose-100/60 sm:p-6"
                aria-labelledby="order-report-partner-heading"
            >
                <h2 id="order-report-partner-heading" class="text-lg font-black text-[#1e2939]">Laporkan toko / mitra</h2>
                <p class="mt-1 text-sm font-semibold text-[#6a7282]">
                    Laporkan masalah terkait pesanan <strong class="text-[#1e2939]">{{ $order->public_order_id }}</strong> ke <strong class="text-[#1e2939]">{{ $order->restaurant_name }}</strong> atau menu <strong class="text-[#1e2939]">{{ $order->box_title }}</strong>. Tim admin dapat meninjaunya.
                </p>
                @if ($errors->reportPartner->any())
                    <ul class="mt-4 list-inside list-disc rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900">
                        @foreach ($errors->reportPartner->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
                <form method="post" action="{{ route('orders.report-partner', ['publicOrderId' => $order->public_order_id]) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="order-track-report-category" class="block text-xs font-black uppercase tracking-wide text-slate-600">Kategori</label>
                        <select
                            id="order-track-report-category"
                            name="category"
                            class="mt-1.5 w-full rounded-xl border-2 border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-[#1e2939] focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        >
                            <option value="">— Pilih (opsional) —</option>
                            <option value="Kualitas makanan" @selected(old('category') === 'Kualitas makanan')>Kualitas makanan</option>
                            <option value="Pelayanan" @selected(old('category') === 'Pelayanan')>Pelayanan</option>
                            <option value="Kebersihan / keamanan" @selected(old('category') === 'Kebersihan / keamanan')>Kebersihan / keamanan</option>
                            <option value="Ketidaksesuaian / penipuan" @selected(old('category') === 'Ketidaksesuaian / penipuan')>Ketidaksesuaian / penipuan</option>
                            <option value="Lainnya" @selected(old('category') === 'Lainnya')>Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label for="order-track-report-message" class="block text-xs font-black uppercase tracking-wide text-slate-600">Detail laporan</label>
                        <textarea
                            id="order-track-report-message"
                            name="message"
                            required
                            rows="4"
                            maxlength="2000"
                            placeholder="Jelaskan masalahnya secara singkat dan jelas…"
                            class="mt-1.5 w-full rounded-xl border-2 border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-[#1e2939] placeholder:text-slate-400 focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        >{{ old('message') }}</textarea>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 px-4 py-3 text-sm font-black text-white shadow-md hover:opacity-95 sm:w-auto"
                    >
                        Kirim laporan
                    </button>
                </form>
            </section>
        @endif

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
            <button type="button" id="restaurant-chat-open"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-[#00a63e] bg-white py-3 text-sm font-black text-[#00a63e] transition hover:bg-[#f0fdf4]">
                <span aria-hidden="true">💬</span>
                Hubungi restoran
            </button>
            <button type="button" id="courier-chat-open"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-[#ff6900] bg-white py-3 text-sm font-black text-[#ff6900] transition hover:bg-orange-50">
                <span aria-hidden="true">💬</span>
                Chat kurir
            </button>
        </div>

        <div id="restaurant-chat-backdrop" class="fixed inset-0 z-50 hidden bg-black/45 p-4 backdrop-blur-[2px]" aria-hidden="true" role="presentation">
            <div
                id="restaurant-chat-panel"
                class="mx-auto mt-4 flex max-h-[calc(100vh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200 sm:mt-12"
                role="dialog"
                aria-modal="true"
                aria-labelledby="restaurant-chat-title"
            >
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 bg-emerald-50/80 px-4 py-3">
                    <div>
                        <h2 id="restaurant-chat-title" class="text-base font-black text-[#1e2939]">Chat admin toko</h2>
                        <p class="text-xs font-semibold text-[#6a7282]">Bot otomatis — tanya apa saja seputar pesanan, restoran, atau SurpriseBite</p>
                    </div>
                    <button type="button" id="restaurant-chat-close" class="rounded-full p-2 text-sm font-black text-slate-600 hover:bg-white/80" aria-label="Tutup">&times;</button>
                </div>
                <div id="restaurant-chat-messages" class="min-h-[240px] flex-1 space-y-3 overflow-y-auto bg-slate-50/60 px-4 py-4"></div>
                <div class="border-t border-slate-100 bg-white p-3">
                    <div class="flex gap-2">
                        <label class="sr-only" for="restaurant-chat-input">Pesan</label>
                        <textarea id="restaurant-chat-input" rows="2" maxlength="2000" placeholder="Tulis pesan ke restoran…"
                                  class="min-h-[44px] flex-1 resize-none rounded-2xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-emerald-200"></textarea>
                        <button type="button" id="restaurant-chat-send"
                                class="shrink-0 self-end rounded-2xl bg-[#00a63e] px-4 py-2 text-sm font-black text-white shadow-md hover:bg-[#008f36]">
                            Kirim
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="courier-chat-backdrop" class="fixed inset-0 z-50 hidden bg-black/45 p-4 backdrop-blur-[2px]" aria-hidden="true" role="presentation">
            <div
                id="courier-chat-panel"
                class="mx-auto mt-4 flex max-h-[calc(100vh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200 sm:mt-12"
                role="dialog"
                aria-modal="true"
                aria-labelledby="courier-chat-title"
            >
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 bg-orange-50/80 px-4 py-3">
                    <div>
                        <h2 id="courier-chat-title" class="text-base font-black text-[#1e2939]">Chat kurir</h2>
                        <p class="text-xs font-semibold text-[#6a7282]">Bot otomatis — tanya apa saja; kami bantu seputar pesanan &amp; pengantaran</p>
                    </div>
                    <button type="button" id="courier-chat-close" class="rounded-full p-2 text-sm font-black text-slate-600 hover:bg-white/80" aria-label="Tutup">&times;</button>
                </div>
                <div id="courier-chat-messages" class="min-h-[240px] flex-1 space-y-3 overflow-y-auto bg-slate-50/60 px-4 py-4"></div>
                <div class="border-t border-slate-100 bg-white p-3">
                    <div class="flex gap-2">
                        <label class="sr-only" for="courier-chat-input">Pesan</label>
                        <textarea id="courier-chat-input" rows="2" maxlength="2000" placeholder="Tulis pesan…"
                                  class="min-h-[44px] flex-1 resize-none rounded-2xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold text-[#1e2939] placeholder:text-slate-400 focus:border-[#ff6900] focus:outline-none focus:ring-2 focus:ring-orange-200"></textarea>
                        <button type="button" id="courier-chat-send"
                                class="shrink-0 self-end rounded-2xl bg-gradient-to-r from-[#ff6900] to-[#ea580c] px-4 py-2 text-sm font-black text-white shadow-md hover:opacity-95">
                            Kirim
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
