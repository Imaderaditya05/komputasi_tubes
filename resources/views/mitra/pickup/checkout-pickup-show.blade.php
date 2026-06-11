<x-layouts.app :title="'Validasi pickup • ' . $order->public_order_id">
@php
    $viteAvail = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
@endphp

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap gap-4 text-sm">
                <a href="{{ route('mitra.checkout-pickups.index', $restaurant) }}" class="font-bold text-[#00a63e] hover:underline">
                    ← Daftar pickup
                </a>
                <a href="{{ route('mitra.restaurants.manage', $restaurant) }}" class="font-semibold text-gray-600 hover:underline">
                    Kelola restoran
                </a>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900" role="alert">
                    @foreach ($errors->all() as $err)
                        <p>{{ $err }}</p>
                    @endforeach
                </div>
            @endif

            @if (! $viteAvail)
                <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-900">
                    Frontend build belum ada — jalankan <code class="rounded bg-white px-1 py-0.5">npm install</code> lalu <code class="rounded bg-white px-1 py-0.5">npm run build</code> untuk skrip realtime penuh. Form validasi tetap bisa dikirim tanpa itu.
                </div>
            @endif

            <div class="rounded-3xl bg-white p-6 shadow ring-1 ring-gray-100 sm:p-8">
                <h1 class="text-2xl font-black text-gray-900">Validasi pickup</h1>
                <p class="mt-2 font-mono text-lg font-black text-[#00a63e]">{{ $order->public_order_id }}</p>

                @php
                    $Pv = \App\Models\CheckoutOrder::PICKUP_VALIDATION_PENDING;
                    $svc = app(\App\Services\PickupValidationService::class);
                    $pickupFvActive = $order->fulfillmentAllowsPickupValidation();
                    $showForm = $pickupFvActive && $order->pickup_validation_status === $Pv;
                    $remain = $order->pickup_validation_status === $Pv
                        ? $svc->remainingSeconds($order->pickup_validation_deadline_at)
                        : 0;
                @endphp

                @php $validationBtnEnabled = $showForm && $remain > 0; @endphp
                <div
                    class="mt-6"
                    id="mitra-pickup-root"
                    data-mitra-pickup-page
                    data-app-timezone="{{ config('app.timezone') }}"
                    data-baseline-status="{{ $order->pickup_validation_status }}"
                    data-baseline-fulfillment="{{ $order->fulfillment_status }}"
                    data-live-url="{{ route('mitra.checkout-pickups.live', ['restaurant' => $restaurant, 'publicOrderId' => $order->public_order_id]) }}"
                    data-initial-show-form="{{ $showForm ? '1' : '0' }}"
                    data-initial-seconds="{{ $remain }}"
                    data-page-server-iso="{{ now()->toIso8601String() }}"
                    data-active-pickup-phases="{{ json_encode(\App\Models\CheckoutOrder::FULFILLMENT_PICKUP_VALIDATION_ACTIVE) }}"
                >
                    <dl class="grid gap-3 rounded-2xl bg-slate-50 p-4 text-sm ring-1 ring-slate-100">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-600">Status pesanan</dt>
                            <dd id="mitra-pv-fulfillment" class="text-right font-bold text-gray-900">
                                <span id="mitra-pv-fulfillment-value">{{ $order->fulfillment_status ?? '—' }}</span>
                                <span class="block text-xs font-semibold normal-case text-gray-500">Pickup · {{ config('app.timezone') }}</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-600">Waktu server (referensi realtime)</dt>
                            <dd id="mitra-pv-server-time" class="font-mono text-xs font-bold text-emerald-800 sm:text-sm">
                                {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-600">Status validasi</dt>
                            <dd id="mitra-pv-status" class="font-bold text-gray-900">
                                @switch($order->pickup_validation_status)
                                    @case(\App\Models\CheckoutOrder::PICKUP_VALIDATION_PENDING)
                                        Menunggu validasi pickup
                                        @break
                                    @case(\App\Models\CheckoutOrder::PICKUP_VALIDATION_EXPIRED)
                                        Kedaluwarsa
                                        @break
                                    @case(\App\Models\CheckoutOrder::PICKUP_VALIDATION_VALIDATED)
                                        Berhasil / selesai
                                        @break
                                    @default
                                        Belum aktif
                                @endswitch
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-600">Mulai jendela validasi</dt>
                            <dd id="mitra-pv-started-text" class="text-right font-bold text-gray-900">
                                @if ($order->pickup_validation_started_at)
                                    {{ $order->pickup_validation_started_at->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}
                                @else
                                    <span class="text-gray-500">— (setelah lunas atau COD sah, pesanan aktif di restoran)</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-600">Batas waktu validasi</dt>
                            <dd id="mitra-pv-deadline-text" class="text-right font-bold text-gray-900">
                                @if ($order->pickup_validation_deadline_at)
                                    {{ $order->pickup_validation_deadline_at->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}
                                @else
                                    <span class="text-gray-500">— (setelah pembayaran &amp; pesanan masuk/alur aktif)</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 border-t border-slate-200 pt-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                            <dt class="text-gray-600">Countdown tersisa</dt>
                            <dd class="text-right">
                                <span id="mitra-pv-remaining" class="font-mono text-lg font-black text-orange-600" data-remain-initial="{{ $remain }}">—</span>
                                <span class="mt-0.5 block text-xs font-semibold text-gray-500">Format HH:mm:ss · disegarkan dari server</span>
                            </dd>
                        </div>
                        @if ($order->pickup_validated_at)
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-600">Submit validasi</dt>
                                <dd class="font-bold text-gray-900">{{ $order->pickup_validated_at->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div id="mitra-pv-alert" class="mt-6 hidden rounded-2xl px-4 py-3 text-sm font-semibold ring-1" role="status"></div>

                    @if (
                        $order->pickup_validation_status === \App\Models\CheckoutOrder::PICKUP_VALIDATION_EXPIRED
                        && $order->fulfillment_method === 'pickup'
                        && ($order->fulfillment_status ?? '') !== 'completed'
                    )
                        <p class="mt-6 rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-900 ring-1 ring-red-100">
                            Batas validasi telah lewat. Submit tidak lagi tersedia.
                        </p>
                    @elseif (
                        ($order->fulfillment_status ?? 'awaiting_payment') === 'awaiting_payment'
                        && ! $showForm
                    )
                        <p class="mt-6 text-sm text-gray-600">
                            Validasi akan tersedia setelah pembayaran <strong>lunas</strong> atau <strong>COD</strong> terverifikasi, lalu pesanan masuk ke alur restoran.
                        </p>
                    @elseif (
                        $order->fulfillment_method === 'pickup'
                        && $order->fulfillment_status !== 'completed'
                        && ! $pickupFvActive
                        && ! $showForm
                    )
                        <p class="mt-6 text-sm text-gray-600">
                            Status pemenuhan tidak mendukung validasi pickup Mitra untuk saat ini. Muat ulang halaman atau hubungi admin jika tidak terduga.
                        </p>
                    @elseif ($order->fulfillment_status === 'completed')
                        <p class="mt-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100">
                            Pesanan selesai. Terima kasih telah memvalidasi pickup.
                        </p>
                    @endif

                    @if ($showForm && $remain > 0)
                        <form
                            id="mitra-pickup-form"
                            method="post"
                            action="{{ route('mitra.checkout-pickups.submit', ['restaurant' => $restaurant, 'publicOrderId' => $order->public_order_id]) }}"
                            class="mt-6 space-y-4"
                        >
                            @csrf
                            <div>
                                <label for="mitra_pickup_validation_note" class="block text-xs font-black uppercase tracking-wide text-gray-700">
                                    Catatan validasi pickup
                                </label>
                                <textarea
                                    id="mitra_pickup_validation_note"
                                    name="pickup_validation_note"
                                    rows="4"
                                    maxlength="2000"
                                    class="mt-2 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                    placeholder="Contoh: Kode pickup cocok / barang telah diserahkan ke pelanggan…"
                                >{{ old('pickup_validation_note') }}</textarea>
                                @error('pickup_validation_note')
                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button
                                type="submit"
                                id="mitra_pickup_submit"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-[#00a63e] px-6 py-3.5 text-sm font-black text-white shadow hover:bg-[#008f36] disabled:pointer-events-none disabled:opacity-50"
                            >
                                <span>Validasi Pesanan</span>
                                <span aria-hidden="true">✓</span>
                            </button>
                        </form>
                    @elseif ($showForm && $remain <= 0)
                        <p class="mt-6 rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-900 ring-1 ring-red-100">
                            Waktu validasi telah habis. Muat ulang halaman untuk memperbarui status dari server.
                        </p>
                    @endif

                    @if (! $validationBtnEnabled)
                        <div class="mt-6 space-y-2">
                            <button
                                type="button"
                                disabled
                                class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-2xl bg-[#00a63e]/40 px-6 py-3.5 text-sm font-black text-white shadow-inner ring-1 ring-emerald-200/60"
                                aria-disabled="true"
                            >
                                Validasi Pesanan
                            </button>
                            @if ($order->fulfillment_status === 'completed')
                                <p class="text-center text-xs font-semibold text-gray-600">
                                    Pesanan sudah diselesaikan — validasi tidak diperlukan lagi.
                                </p>
                            @elseif (
                                $order->pickup_validation_status === \App\Models\CheckoutOrder::PICKUP_VALIDATION_EXPIRED
                                && ($order->fulfillment_status ?? '') !== 'completed'
                            )
                                <p class="text-center text-xs font-semibold text-gray-600">
                                    Jendela validasi kedaluwarsa; tombol tidak dapat diaktifkan.
                                </p>
                            @elseif ($showForm && $remain <= 0)
                                <p class="text-center text-xs font-semibold text-gray-600">
                                    Hitung mundur mencapai nol — muat ulang halaman atau tunggu sinkron server.
                                </p>
                            @else
                                <p class="text-center text-xs font-semibold text-gray-600">
                                    Tombol aktif dari <strong>pengemasan sampai Siap Diambil</strong> selama pembayaran/COD sah dan jendela validasi Mitra sedang berjalan.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@if (! $viteAvail)
    @push('body-scripts')
    <script>
        (function () {
            var root = document.getElementById('mitra-pickup-root');
            if (!root) return;
            var liveUrl = @json(route('mitra.checkout-pickups.live', ['restaurant' => $restaurant, 'publicOrderId' => $order->public_order_id]));
            var baselinePv = root.getAttribute('data-baseline-status') || '';
            var baselineFs = root.getAttribute('data-baseline-fulfillment') || '';
            var remainEl = document.getElementById('mitra-pv-remaining');
            var serverClockEl = document.getElementById('mitra-pv-server-time');
            var startedEl = document.getElementById('mitra-pv-started-text');
            var deadlineEl = document.getElementById('mitra-pv-deadline-text');
            var fulfillmentEl = document.getElementById('mitra-pv-fulfillment-value');
            var appTz = root.getAttribute('data-app-timezone') || '';
            var pickupActiveFs = [];
            try {
                pickupActiveFs = JSON.parse(root.getAttribute('data-active-pickup-phases') || '[]') || [];
            } catch (e) {
                pickupActiveFs = ['pending_confirmation', 'received', 'preparing', 'ready'];
            }
            function pickupFulfillmentAllows(fs) {
                return pickupActiveFs.indexOf(fs) !== -1;
            }
            function pad(n) { return String(Math.max(0, n)).padStart(2, '0'); }
            function fmt(sec) {
                sec = Math.max(0, Math.floor(sec || 0));
                var h = Math.floor(sec / 3600);
                var m = Math.floor((sec % 3600) / 60);
                var s = sec % 60;
                return pad(h) + ':' + pad(m) + ':' + pad(s);
            }
            var sec = Number.parseInt(String(root.getAttribute('data-initial-seconds') || '0'), 10) || 0;
            if (remainEl) remainEl.textContent = sec > 0 ? fmt(sec) : '—';
            var livePv = baselinePv;
            var liveFs = baselineFs;
            var clockAnchorIso = @json(now()->toIso8601String());
            var clockAnchor = {
                ms: Date.parse(String(clockAnchorIso)),
                perf: typeof performance.now === 'function' ? performance.now() : Date.now(),
            };
            function renderServerClockWall() {
                if (!serverClockEl || clockAnchor.ms == null || Number.isNaN(clockAnchor.ms)) return;
                var nowMs =
                    clockAnchor.ms +
                    (typeof performance.now === 'function' ? performance.now() - clockAnchor.perf : 0);
                var t = fmtDtIso(null, nowMs);
                if (t) serverClockEl.textContent = t;
            }
            function fmtDtIso(iso, tsMsOpt) {
                try {
                    var d = iso != null ? new Date(String(iso)) : new Date(Number(tsMsOpt));
                    if (Number.isNaN(d.getTime())) return null;
                    var opts = {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                        timeZone: appTz || undefined,
                    };

                    return new Intl.DateTimeFormat('en-GB', opts).format(d);
                } catch (e) {
                    return null;
                }
            }
            setInterval(renderServerClockWall, 1000);
            renderServerClockWall();

            setInterval(function () {
                if (livePv === 'pickup_validation_pending' && pickupFulfillmentAllows(liveFs) && sec > 0) {
                    sec -= 1;
                }
                if (livePv === 'pickup_validation_pending' && pickupFulfillmentAllows(liveFs) && remainEl) {
                    remainEl.textContent = sec > 0 ? fmt(sec) : '—';
                }
                var btn = document.getElementById('mitra_pickup_submit');
                if (btn && sec <= 0 && livePv === 'pickup_validation_pending' && pickupFulfillmentAllows(liveFs)) {
                    btn.disabled = true;
                }
            }, 1000);

            async function poll() {
                try {
                    var res = await fetch(liveUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                    if (!res.ok) return;
                    var envelope = await res.json();
                    var live = envelope && envelope.pickup_live;
                    if (!live) return;
                    var pv = live.pickup_validation_status || '';
                    var fs = live.fulfillment_status || '';
                    livePv = pv;
                    liveFs = fs;
                    var isoSrv = typeof live.server_time_iso === 'string' ? live.server_time_iso : '';
                    if (isoSrv) {
                        var parsedSrv = Date.parse(isoSrv);
                        if (!Number.isNaN(parsedSrv)) {
                            clockAnchor.ms = parsedSrv;
                            clockAnchor.perf = typeof performance.now === 'function' ? performance.now() : Date.now();
                            renderServerClockWall();
                        }
                    }
                    if (fulfillmentEl && fs) fulfillmentEl.textContent = fs;
                    if (startedEl && live.pickup_validation_started_at) {
                        var st = fmtDtIso(live.pickup_validation_started_at);
                        if (st) startedEl.textContent = st;
                    }
                    if (deadlineEl && live.pickup_validation_deadline_at) {
                        var dl = fmtDtIso(live.pickup_validation_deadline_at);
                        if (dl) deadlineEl.textContent = dl;
                    }
                    if (pv !== baselinePv || fs !== baselineFs) {
                        window.location.reload();
                        return;
                    }
                    if (
                        typeof live.pickup_validation_seconds_remaining === 'number'
                        && pv === 'pickup_validation_pending'
                        && pickupFulfillmentAllows(fs)
                    ) {
                        sec = live.pickup_validation_seconds_remaining;
                        if (remainEl) remainEl.textContent = fmt(sec);
                    }
                } catch (e) { /* noop */ }
            }
            poll();
            setInterval(poll, 6000);
        })();
    </script>
    @endpush
@endif

</x-layouts.app>
