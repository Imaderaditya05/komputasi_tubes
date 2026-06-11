<x-layouts.app :title="'Pickup (checkout) • ' . $restaurant->name">
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('mitra.restaurants.manage', $restaurant) }}" class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-[#00a63e] hover:underline">
                ← Kembali ke kelola restoran
            </a>

            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <h1 class="mb-2 text-2xl font-black text-gray-900">Pickup (checkout SurpriseBite)</h1>
            <p class="mb-8 text-sm text-gray-600">
                Validasi pengambilan sebelum pesanan ditandai selesai. Batas waktu dihitung di server setelah pembayaran/COD sah, saat pesanan aktif di alur restoran (mulai konfirmasi hingga siap diambil).
            </p>

            @if ($orders->isEmpty())
                <div class="rounded-2xl bg-white p-10 text-center shadow ring-1 ring-gray-100 sm:p-12">
                    <p class="text-lg font-black text-gray-900">Belum ada pesanan pickup SurpriseBite</p>
                    <p class="mt-3 max-w-xl mx-auto text-sm text-gray-600">
                        Halaman ini hanya menampilkan <strong>checkout pelanggan</strong> untuk mystery box Anda dengan metode <strong>Pickup</strong>
                        dan <strong>Order ID surprise bite</strong> (bukan entri pesanan lain di panel mitra).
                    </p>
                    <p class="mt-4 text-sm text-gray-600">
                        Uji alur dengan checkout pickup dari beranda/browse, lakukan pembayaran (atau COD), lalu mitra dapat memvalidasi selama status pesanan masih dalam alur pickup aktif (konfirmasi → diterima → disiapkan → siap diambil).
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($orders as $o)
                        @php
                            $pv = $o->pickup_validation_status;
                            $fs = $o->fulfillment_status;
                            $badge = match (true) {
                                $pv === \App\Models\CheckoutOrder::PICKUP_VALIDATION_VALIDATED || $fs === 'completed'
                                    => ['label' => 'Validasi berhasil · Selesai', 'tone' => 'emerald'],
                                $pv === \App\Models\CheckoutOrder::PICKUP_VALIDATION_EXPIRED
                                    => ['label' => 'Validasi kedaluwarsa', 'tone' => 'red'],
                                $pv === \App\Models\CheckoutOrder::PICKUP_VALIDATION_PENDING && $o->fulfillmentAllowsPickupValidation()
                                    => ['label' => 'Menunggu validasi pickup', 'tone' => 'amber'],
                                $fs === 'ready' && $o->fulfillment_method === 'pickup'
                                    && ($pv ?? '') !== \App\Models\CheckoutOrder::PICKUP_VALIDATION_PENDING
                                    => ['label' => 'Siap diambil (siap)', 'tone' => 'orange'],
                                default => ['label' => $fs ?? '—', 'tone' => 'slate'],
                            };
                            $toneClass = [
                                'emerald' => 'bg-emerald-50 text-emerald-900 ring-emerald-200',
                                'red' => 'bg-red-50 text-red-800 ring-red-200',
                                'amber' => 'bg-amber-50 text-amber-900 ring-amber-200',
                                'orange' => 'bg-orange-50 text-orange-900 ring-orange-200',
                                'slate' => 'bg-slate-100 text-slate-800 ring-slate-200',
                            ][$badge['tone']];
                        @endphp
                        <article class="rounded-2xl bg-white p-5 shadow ring-1 ring-gray-100 sm:p-6">
                            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Order ID</p>
                                    <p class="font-mono text-lg font-black text-[#00a63e]">{{ $o->public_order_id }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $toneClass }}">
                                    {{ $badge['label'] }}
                                </span>
                            </div>
                            <p class="mb-4 text-sm text-gray-700">
                                <span class="font-bold">Box:</span> {{ $o->box_title }}
                            </p>
                            <a
                                href="{{ route('mitra.checkout-pickups.show', ['restaurant' => $restaurant, 'publicOrderId' => $o->public_order_id]) }}"
                                class="inline-flex items-center rounded-xl bg-[#00a63e] px-4 py-2 text-sm font-black text-white shadow hover:bg-[#008f36]"
                            >
                                Buka detail / validasi
                                <span class="ml-1" aria-hidden="true">→</span>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
