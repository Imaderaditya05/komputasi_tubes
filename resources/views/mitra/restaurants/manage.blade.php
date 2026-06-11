<x-layouts.app :title="'Kelola ' . $restaurant->name">
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header Bar -->
            <div class="bg-gray-900 rounded-2xl shadow-xl overflow-hidden mb-8">
                <div class="p-6 sm:p-8 flex flex-col sm:flex-row justify-between items-center text-white">
                    <div class="flex items-center space-x-4 mb-4 sm:mb-0">
                        <div class="bg-blue-500/20 p-3 rounded-xl border border-blue-400/30">
                            <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black">{{ $restaurant->name }}</h2>
                            <p class="text-gray-400 text-sm mt-1">Unlocked Mode (Aman)</p>
                        </div>
                    </div>
                    
                    <form action="{{ route('mitra.restaurants.lock', $restaurant) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-lg text-white bg-red-600 hover:bg-red-700 shadow-sm transition">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Lock & Keluar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats & Navigation -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8"
                data-mitra-manage-live
                data-mitra-fingerprint="{{ $mitraLiveHash }}"
                data-mitra-live-url="{{ route('mitra.api.live.restaurant', $restaurant) }}"
            >
                <a href="{{ route('restaurants.menus.index', $restaurant) }}" class="bg-white rounded-2xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex items-center justify-between group">
                    <div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Total Menu</p>
                        <p class="text-4xl font-black text-gray-900 mt-2" data-mitra-manage-menus-count>{{ $menusCount }}</p>
                    </div>
                    <div class="bg-blue-50 text-blue-600 p-4 rounded-full group-hover:scale-110 transition">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </div>
                </a>

                <a href="{{ route('restaurants.orders.index', $restaurant) }}" class="bg-white rounded-2xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex items-center justify-between group">
                    <div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Pesanan Masuk</p>
                        <p class="text-4xl font-black text-gray-900 mt-2" data-mitra-manage-orders-count>{{ $ordersCount }}</p>
                    </div>
                    <div class="bg-green-50 text-green-600 p-4 rounded-full group-hover:scale-110 transition">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                </a>

                <a href="{{ route('mitra.checkout-deliveries', $restaurant) }}" class="bg-white rounded-2xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex items-center justify-between group md:col-span-2">
                    <div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Lacak delivery (pelanggan)</p>
                        <p class="mt-2 text-sm font-semibold text-gray-700">Kirim koordinat GPS kurir untuk peta realtime</p>
                    </div>
                    <div class="bg-orange-50 text-orange-600 p-4 rounded-full group-hover:scale-110 transition">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </a>

                <a href="{{ route('mitra.checkout-pickups.index', $restaurant) }}" class="bg-white rounded-2xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex items-center justify-between group md:col-span-2">
                    <div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Validasi pickup (checkout)</p>
                        <p class="mt-2 text-sm font-semibold text-gray-700">Konfirmasi pengambilan sebelum pesanan selesai</p>
                    </div>
                    <div class="bg-amber-50 text-amber-600 p-4 rounded-full group-hover:scale-110 transition">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a2 2 0 002-2h-4a2 2 0 002 2z"/></svg>
                    </div>
                </a>
            </div>

            <!-- Ringkasan ulasan & laporan pelanggan -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow border border-gray-100 md:col-span-2">
                    <h3 class="text-lg font-black text-gray-900">Rating & ulasan pelanggan</h3>
                    <p class="text-sm text-gray-500 mt-1">Gabungan skor untuk semua mystery box dari restoran ini.</p>
                    <div class="mt-6 flex flex-wrap gap-8">
                        @if(($feedbackSummary['count'] ?? 0) > 0)
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rata-rata</p>
                                <p class="text-4xl font-black text-amber-500 mt-1">{{ number_format((float) $feedbackSummary['avg'], 1) }}<span class="text-xl text-gray-400"> /5</span></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah ulasan</p>
                                <p class="text-4xl font-black text-gray-900 mt-1">{{ $feedbackSummary['count'] }}</p>
                            </div>
                        @else
                            <p class="text-sm text-gray-600">Belum ada ulasan bergizi (rating 1–5) untuk menu restoran Anda.</p>
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow border border-gray-100">
                    <h3 class="text-lg font-black text-gray-900">Laporan ke Surprise Bite</h3>
                    <p class="text-sm text-gray-500 mt-1">Keluh/kategori dari pelanggan setelah pesanan selesai.</p>
                    <div class="mt-6">
                        @if($customerReports->isEmpty())
                            <p class="text-sm text-gray-600">Belum ada laporan masuk untuk restoran ini.</p>
                        @else
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Menunggu tinjauan admin</p>
                            <p class="text-4xl font-black text-orange-600 mt-1">{{ $pendingReportsCount }}</p>
                            <p class="text-xs text-gray-500 mt-2">Menampilkan hingga {{ $customerReports->count() }} laporan terbaru di bawah.</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($perMenuRatings->isNotEmpty())
                <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-black text-gray-900">Rating per menu</h3>
                        <p class="text-sm text-gray-500">Rincian dari ulasan checkout (mitra-menu).</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-600 text-left uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 font-bold">Menu</th>
                                    <th class="px-6 py-3 font-bold">Rata-rata</th>
                                    <th class="px-6 py-3 font-bold">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($perMenuRatings as $m)
                                    <tr class="hover:bg-gray-50/80">
                                        <td class="px-6 py-3 font-semibold text-gray-900">{{ $m->name }}</td>
                                        <td class="px-6 py-3 text-gray-700">
                                            @if(($m->ratings_count ?? 0) > 0 && $m->avg_rating !== null)
                                                {{ number_format((float) $m->avg_rating, 2) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-gray-700">{{ (int) ($m->ratings_count ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($recentReviews->isNotEmpty())
                <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-black text-gray-900">Ulasan terbaru</h3>
                        <p class="text-sm text-gray-500">Dari pesanan checkout (nama pelanggan disamarkan).</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-600 text-left uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 font-bold">Menu</th>
                                    <th class="px-6 py-3 font-bold">Pelanggan</th>
                                    <th class="px-6 py-3 font-bold">Rating</th>
                                    <th class="px-6 py-3 font-bold">Komentar</th>
                                    <th class="px-6 py-3 font-bold">Diperbarui</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($recentReviews as $order)
                                    @php
                                        $mid = preg_match('/^mitra-menu-(\d+)$/', (string) $order->box_slug, $mm) ? (int) $mm[1] : null;
                                        $itemName = $mid && isset($menuNamesById[$mid]) ? $menuNamesById[$mid] : ((string) ($order->box_title ?: $order->box_slug));
                                        $maskedEmail = \App\Services\MitraRestaurantFeedbackService::maskEmail($order->customer_email);
                                        $comment = $order->customer_review_comment;
                                    @endphp
                                    <tr class="hover:bg-gray-50/80 align-top">
                                        <td class="px-6 py-3 font-medium text-gray-900">{{ $itemName }}</td>
                                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap">{{ $maskedEmail }}</td>
                                        <td class="px-6 py-3">
                                            @if($order->customer_rating !== null && (int) $order->customer_rating >= 1)
                                                <span class="text-amber-500 font-bold" aria-label="Rating {{ $order->customer_rating }} dari 5">{{ str_repeat('★', (int) $order->customer_rating) }}{{ str_repeat('☆', max(0, 5 - (int) $order->customer_rating)) }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-gray-700 max-w-xs">
                                            @if($comment)
                                                {{ \Illuminate\Support\Str::limit((string) $comment, 200) }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-gray-500 whitespace-nowrap text-xs">{{ $order->updated_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($customerReports->isNotEmpty())
                <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-black text-gray-900">Laporan pelanggan</h3>
                        <p class="text-sm text-gray-500">Yang dikirim dari halaman pelacakan pesanan. Status ditangani oleh admin.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-600 text-left uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 font-bold">Tanggal</th>
                                    <th class="px-6 py-3 font-bold">Kategori</th>
                                    <th class="px-6 py-3 font-bold">Pesan</th>
                                    <th class="px-6 py-3 font-bold">Status</th>
                                    <th class="px-6 py-3 font-bold">Pelapor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($customerReports as $report)
                                    @php
                                        $repName = optional($report->reporter)->name ?: 'Pelanggan';
                                        $repEmail = optional($report->reporter)->email;
                                        $repMasked = \App\Services\MitraRestaurantFeedbackService::maskEmail($repEmail);
                                        $status = (string) ($report->status ?? 'pending');
                                        $statusLabel = match ($status) {
                                            'pending' => 'Menunggu tinjauan',
                                            'resolved' => 'Diselesaikan',
                                            'closed' => 'Ditutup',
                                            default => $status,
                                        };
                                        $badgeClass = match ($status) {
                                            'pending' => 'bg-amber-100 text-amber-900',
                                            'resolved', 'closed' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50/80 align-top">
                                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap text-xs">{{ $report->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                        <td class="px-6 py-3 text-gray-800">{{ $report->category ?: '—' }}</td>
                                        <td class="px-6 py-3 text-gray-700 max-w-md">{{ \Illuminate\Support\Str::limit((string) $report->message, 280) }}</td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold {{ $badgeClass }}" title="{{ $status }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 text-xs">{{ $repName }}<span class="block text-gray-400">{{ $repMasked }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-layouts.app>
