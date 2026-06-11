@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $placeholderImg = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80';
@endphp

<x-layouts.app title="Dashboard Mitra — SurpriseBite" variant="marketing" :mitra-store-name="$restaurant?->name">
    <div class="min-h-[calc(100vh-5rem)] bg-gradient-to-b from-emerald-50/80 to-[#fafafa] pb-16">
        <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Header dashboard -->
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="flex flex-wrap items-center gap-2 text-3xl font-black tracking-tight text-[#1e2939] sm:text-4xl">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </span>
                        Dashboard Mitra
                    </h1>
                    @if($restaurant)
                        <p class="mt-2 flex items-center gap-2 text-base font-semibold text-slate-600">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            {{ $restaurant->name }}
                        </p>
                    @else
                        <p class="mt-2 text-slate-600">Buat restoran terlebih dahulu untuk mengelola Mystery Box.</p>
                    @endif
                </div>
                <form method="post" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-red-500 px-6 py-3 text-sm font-bold text-white shadow-md shadow-red-500/25 transition hover:bg-red-600 sm:w-auto">
                        Logout
                    </button>
                </form>
            </div>

            @if($restaurant)
                @php
                    $mitraStatsMenusPayload = $menus->map(fn ($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                        'stock' => (int) $m->stock,
                        'price' => (float) $m->price,
                        'original_price' => (float) $m->original_price,
                        'savings_percent' => $m->savingsPercent(),
                    ])->values()->all();
                @endphp
                <div
                    id="mitra-live-root"
                    data-mitra-dashboard-live
                    data-mitra-fingerprint="{{ $mitraLiveHash }}"
                    data-mitra-live-url="{{ route('mitra.api.live.restaurant', $restaurant) }}"
                    data-mitra-sales-period="{{ $salesPeriod }}"
                    data-placeholder-img="{{ $placeholderImg }}"
                >
                <!-- Statistik -->
                <p class="mb-3 text-xs font-semibold text-slate-600">Klik salah satu kartu statistik di bawah untuk melihat rincian lengkap dan tabel per mystery box.</p>
                <div class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" id="mitra-stats">
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 cursor-pointer select-none"
                        data-stat-card="boxes"
                        role="button"
                        tabindex="0"
                        aria-label="Rincian total mystery box"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Mystery Box</p>
                                <p class="stat-total-boxes text-3xl font-black text-emerald-600" data-value="{{ $stats['total_boxes'] }}">{{ $stats['total_boxes'] }}</p>
                                <p class="mt-1 text-[11px] font-medium text-slate-400">Klik untuk rincian</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 cursor-pointer select-none"
                        data-stat-card="stock"
                        role="button"
                        tabindex="0"
                        aria-label="Rincian total stok"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Stok</p>
                                <p class="stat-total-stock text-3xl font-black text-blue-600" data-value="{{ $stats['total_stock'] }}">{{ $stats['total_stock'] }}</p>
                                <p class="mt-1 max-w-[14rem] text-[11px] font-medium leading-snug text-slate-400">Penjumlahan stok tersisa dari setiap mystery box di bawah (bukan stok per tipe menu saja).</p>
                                <p class="mt-1 text-[11px] font-medium text-emerald-600/90">Klik untuk rincian</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 cursor-pointer select-none"
                        data-stat-card="revenue"
                        role="button"
                        tabindex="0"
                        aria-label="Rincian estimasi revenue"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Estimasi Revenue</p>
                                <p class="stat-revenue text-2xl font-black text-orange-600 sm:text-3xl" data-value="{{ $stats['revenue_estimate'] }}">{{ $rp($stats['revenue_estimate']) }}</p>
                                <p class="mt-1 text-[11px] font-medium text-slate-400">Klik untuk rincian</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 cursor-pointer select-none"
                        data-stat-card="avg"
                        role="button"
                        tabindex="0"
                        aria-label="Rincian rata-rata hemat"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Avg. Savings</p>
                                <p class="stat-avg-savings text-2xl font-black text-violet-600 sm:text-3xl" data-value="{{ $stats['avg_savings'] }}">{{ $rp($stats['avg_savings']) }}</p>
                                <p class="mt-1 text-[11px] font-medium text-slate-400">Klik untuk rincian</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Performa penjualan checkout (sungguhan) --}}
                <div class="mb-10 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-xl font-black text-[#1e2939]">Performa Penjualan</h2>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-600 stat-sales-period-label">{{ $sales['period_label'] ?? '' }}</span>
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                Omzet dihitung dari pesanan dengan pembayaran <strong>Lunas</strong> atau <strong>COD sah</strong> (selaras penyusutan stok/pemantau dampak platform).
                                Filter memakai <strong>tanggal pesanan dibuat</strong>; pesanan baru yang belum bayar online akan muncul di «Menunggu bayar (online)».
                            </p>
                        </div>
                        <form method="get" action="{{ route('mitra.dashboard') }}" class="shrink-0">
                            <label for="sales_period" class="sr-only">Periode statistik penjualan</label>
                            <select id="sales_period" name="sales_period" onchange="this.form.submit()" class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-[#1e2939] shadow-sm outline-none ring-emerald-500/0 transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 sm:w-auto lg:min-w-[14rem]">
                                <option value="today" @selected($salesPeriod === 'today')>Hari ini</option>
                                <option value="7d" @selected($salesPeriod === '7d')>7 hari terakhir</option>
                                <option value="30d" @selected($salesPeriod === '30d')>30 hari terakhir</option>
                                <option value="month" @selected($salesPeriod === 'month')>Bulan ini</option>
                                <option value="all" @selected($salesPeriod === 'all')>Sepanjang waktu</option>
                            </select>
                        </form>
                    </div>

                    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 ring-1 ring-emerald-100/80">
                            <p class="text-xs font-black uppercase tracking-wide text-emerald-800/70">Omzet</p>
                            <p class="stat-sales-gross mt-1 text-2xl font-black tracking-tight text-emerald-800" data-value="{{ $sales['gross_idr'] ?? 0 }}">{{ $rp($sales['gross_idr'] ?? 0) }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-5">
                            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Transaksi lunas/COD</p>
                            <p class="stat-sales-orders mt-1 text-3xl font-black text-slate-900" data-value="{{ $sales['order_count'] ?? 0 }}">{{ $sales['order_count'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-white p-5">
                            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Unit terjual</p>
                            <p class="stat-sales-units mt-1 text-3xl font-black text-teal-700" data-value="{{ $sales['units_sold'] ?? 0 }}">{{ $sales['units_sold'] ?? 0 }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">Σ barang dari jumlah per pesanan.</p>
                        </div>
                        <div class="rounded-2xl border border-violet-100 bg-violet-50/70 p-5">
                            <p class="text-xs font-black uppercase tracking-wide text-violet-900/70">Keranjang rata-rata</p>
                            <p class="stat-sales-aov mt-1 text-2xl font-black text-violet-900" data-value="{{ $sales['avg_order_idr'] ?? 0 }}">{{ $rp($sales['avg_order_idr'] ?? 0) }}</p>
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-5">
                            <p class="text-xs font-black uppercase tracking-wide text-amber-900/70">Menunggu bayar (online)</p>
                            <p class="stat-sales-pending-orders mb-1 text-lg font-black text-amber-900" data-value="{{ $sales['pending_gateway_count'] ?? 0 }}">{{ $sales['pending_gateway_count'] ?? 0 }}</p>
                            <p class="stat-sales-pending-gross text-sm font-bold text-amber-950/85" data-value="{{ $sales['pending_gateway_idr'] ?? 0 }}">{{ $rp($sales['pending_gateway_idr'] ?? 0) }}</p>
                        </div>
                    </div>

                    <div class="mb-8 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm font-semibold text-emerald-900">
                        <span>Pesanan <strong>penjualan tercatat</strong> yang sudah ditandai <strong>selesai</strong> di checkout: <strong class="stat-sales-completed">{{ (int) ($sales['completed_orders'] ?? 0) }}</strong> transaksi dalam periode.</span>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3 sm:px-5">
                            <h3 class="text-sm font-black uppercase tracking-wide text-slate-600">Omzet per mystery box</h3>
                            <a href="{{ route('restaurants.orders.index', $restaurant) }}" class="rounded-full bg-slate-900 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-slate-800">Kelola &amp; status pesanan</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-slate-100 bg-white text-[11px] font-black uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="whitespace-nowrap px-4 py-3">Menu</th>
                                        <th class="whitespace-nowrap px-4 py-3">Order</th>
                                        <th class="whitespace-nowrap px-4 py-3">Unit</th>
                                        <th class="whitespace-nowrap px-4 py-3">Omzet</th>
                                        <th class="whitespace-nowrap px-4 py-3 lg:w-56"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($sales['by_menu'] ?? [] as $row)
                                        @php($mid = isset($row['menu_id']) && $row['menu_id'] ? (int) $row['menu_id'] : null)
                                        @php($editHref = $mid ? route('restaurants.menus.edit', [$restaurant, $mid]) : null)
                                        @php($boxHref = $mid ? route('boxes.show', 'mitra-menu-'.$mid) : null)
                                        <tr class="transition hover:bg-slate-50/90">
                                            <td class="px-4 py-3 font-bold text-slate-900">{{ $row['name'] ?? '—' }}</td>
                                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $row['orders'] ?? 0 }}</td>
                                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $row['units'] ?? 0 }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 font-black text-emerald-700">{{ $rp($row['gross_idr'] ?? 0) }}</td>
                                            <td class="px-4 py-3">
                                                @if ($boxHref !== null || $editHref !== null)
                                                    <div class="flex flex-wrap gap-2 justify-end">
                                                        @if ($boxHref !== null)
                                                            <a href="{{ $boxHref }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-black text-white hover:bg-emerald-700">Lihat box</a>
                                                        @endif
                                                        @if ($editHref !== null)
                                                            <a href="{{ $editHref }}" class="rounded-full border-2 border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-[#1e2939] hover:border-emerald-300">Kelola menu</a>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-xs text-slate-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-10 text-center font-semibold text-slate-600">Belum ada penjualan tercatat pada periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-10 flex flex-wrap gap-3">
                    <a
                        href="{{ route('mitra.restaurants.manage', $restaurant) }}"
                        class="inline-flex items-center justify-center rounded-2xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-black text-[#1e2939] shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50/40"
                    >
                        Kelola restoran
                    </a>
                    <a href="{{ route('restaurants.orders.index', $restaurant) }}" class="inline-flex items-center justify-center rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-800 shadow-sm transition hover:border-emerald-400 hover:bg-emerald-100/80">
                        Semua pesanan checkout
                    </a>
                    <a
                        href="{{ route('mitra.checkout-deliveries', $restaurant) }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-4 py-3 text-sm font-black text-white shadow-md transition hover:bg-orange-600"
                    >
                        Pesanan delivery
                    </a>
                    <a
                        href="{{ route('mitra.checkout-pickups.index', $restaurant) }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-4 py-3 text-sm font-black text-white shadow-md transition hover:bg-amber-600"
                    >
                        Validasi pickup
                    </a>
                </div>

                <!-- Mystery Box -->
                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-xl font-black text-[#1e2939]">Mystery Box Saya</h2>
                        <button type="button" id="btn-open-mystery-create"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-900/15 transition hover:opacity-95">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Tambah Mystery Box
                        </button>
                    </div>

                    <div id="mystery-empty" class="@if($menus->isNotEmpty()) hidden @endif rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/80 py-16 text-center">
                        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-200/80 text-slate-400">
                            <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.25"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                        <p class="text-lg font-bold text-slate-600">Belum ada Mystery Box</p>
                        <button type="button" class="btn-open-mystery-create mt-4 inline-flex items-center rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-6 py-2.5 text-sm font-bold text-white shadow-md">
                            Tambah Mystery Box Pertama
                        </button>
                    </div>

                    <div id="mystery-grid" class="grid grid-cols-1 gap-6 md:grid-cols-2 @if($menus->isEmpty()) hidden @endif">
                        @foreach($menus as $menu)
                            @include('mitra.partials.mystery-card', ['menu' => $menu, 'placeholderImg' => $placeholderImg, 'rp' => $rp])
                        @endforeach
                    </div>
                </div>
                </div>
            @endif

            <!-- Buat restoran (jika belum ada) -->
            @if($restaurants->isEmpty())
                <div class="mt-10 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-lg font-black text-[#1e2939]">Buat Restoran Baru</h2>
                    <p class="mt-1 text-sm text-slate-600">Satu akun mitra dapat memiliki satu atau lebih restoran. Mulai dengan menambahkan nama warung Anda.</p>
                    <form action="{{ route('mitra.restaurants.store') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Nama Restoran</label>
                                <input type="text" name="name" required value="{{ old('name') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">PIN Rahasia (Untuk Unlock)</label>
                                <input type="password" name="pin" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Minimal 4 karakter">
                                @error('pin') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700">Deskripsi</label>
                                <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                                @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <button type="submit" class="inline-flex rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-emerald-700">
                            Buat Restoran
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @if($restaurant)
        <!-- Modal tambah -->
        <div id="modal-mystery-create" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" aria-hidden="true">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-black text-[#1e2939]">Tambah Mystery Box</h3>
                    <button type="button" class="modal-close rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form id="form-mystery-create" class="space-y-4 px-6 py-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Nama Box</label>
                        <input name="name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Harga Jual</label>
                            <input name="price" type="number" min="0" step="1" value="0" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Nilai Asli</label>
                            <input name="original_price" type="number" min="0" step="1" value="0" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Kategori</label>
                        <input name="category" placeholder="e.g., Nasi Goreng, Sushi, Bakery" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Deskripsi</label>
                        <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"></textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Stok Tersedia</label>
                            <input name="stock" type="number" min="0" step="1" value="0" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Waktu Pickup</label>
                            <input name="pickup_time" placeholder="e.g., 18:00-20:00" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">URL Gambar (opsional)</label>
                        <input name="image_url" type="text" placeholder="https://..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" class="modal-close rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-5 py-2 text-sm font-bold text-white shadow">Tambah Mystery Box</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal edit -->
        <div id="modal-mystery-edit" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-black text-[#1e2939]">Edit Mystery Box</h3>
                    <button type="button" class="modal-close rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form id="form-mystery-edit" class="space-y-4 px-6 py-4">
                    <input type="hidden" name="menu_id" id="edit-menu-id">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Nama Box</label>
                        <input name="name" id="edit-name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Harga Jual</label>
                            <input name="price" id="edit-price" type="number" min="0" step="1" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Nilai Asli</label>
                            <input name="original_price" id="edit-original_price" type="number" min="0" step="1" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Kategori</label>
                        <input name="category" id="edit-category" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Deskripsi</label>
                        <textarea name="description" id="edit-description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"></textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Stok Tersedia</label>
                            <input name="stock" id="edit-stock" type="number" min="0" step="1" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Waktu Pickup</label>
                            <input name="pickup_time" id="edit-pickup_time" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">URL Gambar (opsional)</label>
                        <input name="image_url" id="edit-image_url" type="text" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" class="modal-close rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-5 py-2 text-sm font-bold text-white shadow">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ringkasan statistik (kartu atas) -->
        <div id="modal-mitra-stats-overview" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" aria-hidden="true">
            <div class="max-h-[92vh] w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl flex flex-col">
                <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-black text-[#1e2939]">Ringkasan statistik</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Angka dihitung dari semua mystery box restoran Anda saat ini.</p>
                    </div>
                    <button type="button" class="modal-close rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div id="modal-mitra-stats-body" class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-slate-100 bg-emerald-50/50 p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Mystery box</p>
                            <p id="overview-recap-boxes" class="text-2xl font-black text-emerald-600">0</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-blue-50/50 p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Total stok</p>
                            <p id="overview-recap-stock" class="text-2xl font-black text-blue-600">0</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-orange-50/50 p-3 text-center sm:col-span-2">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Est. revenue</p>
                            <p id="overview-recap-revenue" class="text-lg font-black text-orange-600 sm:text-xl">Rp 0</p>
                        </div>
                        <div class="col-span-2 rounded-xl border border-slate-100 bg-violet-50/50 p-3 text-center sm:col-span-4">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Rata-rata hemat / box</p>
                            <p id="overview-recap-avg" class="text-lg font-black text-violet-600">Rp 0</p>
                        </div>
                    </div>

                    <div id="stat-section-boxes" class="mt-6 rounded-xl border border-slate-100 bg-white p-4">
                        <h4 class="text-sm font-black text-slate-800">Total Mystery Box</h4>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">Jumlah entri mystery box yang Anda kelola. Sama dengan jumlah baris pada tabel per box di bawah.</p>
                    </div>
                    <div id="stat-section-stock" class="mt-4 rounded-xl border border-slate-100 bg-white p-4">
                        <h4 class="text-sm font-black text-slate-800">Total Stok</h4>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">Semua angka stok dari setiap box dijumlahkan. Jika Anda punya beberapa box, total bisa lebih besar dari stok satu kartu saja.</p>
                    </div>
                    <div id="stat-section-revenue" class="mt-4 rounded-xl border border-slate-100 bg-white p-4">
                        <h4 class="text-sm font-black text-slate-800">Estimasi Revenue</h4>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">Perkiraan pendapatan kotor jika <strong>semua unit terjual</strong> pada harga jual saat ini: Σ (harga jual × stok) per mystery box.</p>
                    </div>
                    <div id="stat-section-avg" class="mt-4 rounded-xl border border-slate-100 bg-white p-4">
                        <h4 class="text-sm font-black text-slate-800">Avg. Savings</h4>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">Rata-rata nilai hemat per box: rata-rata dari (nilai asli − harga jual) untuk setiap mystery box.</p>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-sm font-black text-slate-800">Rincian per mystery box</h4>
                        <p class="mt-1 text-xs text-slate-500">Data diperbarui otomatis bersama dashboard.</p>
                        <div class="-mx-1 mt-3 overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-600">
                                    <tr>
                                        <th class="px-3 py-2.5">#</th>
                                        <th class="px-3 py-2.5">Nama</th>
                                        <th class="px-3 py-2.5 text-right">Stok</th>
                                        <th class="px-3 py-2.5 text-right">Harga</th>
                                        <th class="px-3 py-2.5 text-right">Nilai asli</th>
                                        <th class="px-3 py-2.5 text-right">Hemat %</th>
                                        <th class="px-3 py-2.5 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="mitra-stats-breakdown-tbody" class="divide-y divide-slate-100 bg-white"></tbody>
                                <tfoot id="mitra-stats-breakdown-foot" class="border-t-2 border-slate-200 bg-slate-50 font-semibold"></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal detail -->
        <div id="modal-mystery-detail" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" aria-hidden="true">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-black text-[#1e2939]">Detail Mystery Box</h3>
                    <button type="button" class="modal-close rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="space-y-4 px-6 py-4">
                    <div class="overflow-hidden rounded-xl bg-slate-100">
                        <img id="detail-img" src="" alt="" class="aspect-[16/10] w-full object-cover">
                    </div>
                    <div>
                        <h4 id="detail-name" class="text-xl font-black text-slate-900"></h4>
                        <p id="detail-category" class="mt-1 hidden text-sm font-semibold text-emerald-600"></p>
                        <p id="detail-description" class="mt-2 text-sm leading-relaxed text-slate-600"></p>
                    </div>
                    <dl class="space-y-2 rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Harga jual</dt>
                            <dd id="detail-price" class="font-bold text-emerald-600"></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Nilai asli</dt>
                            <dd id="detail-original" class="text-slate-400 line-through"></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Hemat</dt>
                            <dd id="detail-hemat" class="font-bold text-orange-500"></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Stok tersisa</dt>
                            <dd id="detail-stock" class="font-semibold text-slate-900"></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Waktu pickup</dt>
                            <dd id="detail-pickup" class="font-semibold text-slate-700"></dd>
                        </div>
                    </dl>
                    <a id="detail-public-link" href="#" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 py-2.5 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100">
                        Lihat halaman publik (Browse)
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal hapus -->
        <div id="modal-mystery-delete" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="bg-red-500 px-6 py-4 text-white">
                    <div class="flex items-center gap-2 font-black">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Hapus Mystery Box?
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="flex gap-3">
                        <img id="delete-preview-img" src="" alt="" class="h-16 w-16 rounded-lg object-cover bg-slate-100">
                        <div>
                            <p id="delete-item-title" class="font-bold text-slate-900"></p>
                            <p id="delete-item-price" class="text-sm text-emerald-600"></p>
                        </div>
                    </div>
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan! Mystery box akan dihapus permanen dari sistem.
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="modal-close rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="button" id="btn-confirm-delete" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white shadow hover:bg-red-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const storeUrl = @json(route('mitra.mystery-boxes.store', $restaurant));
                const placeholderImg = @json($placeholderImg);
                const updateUrl = (id) => @json(url('/mitra/restaurants/'.$restaurant->id.'/mystery-boxes')).replace(/\/+$/, '') + '/' + id;
                const publicBoxUrl = (menuId) => @json(url('/boxes')) + '/mitra-menu-' + menuId;


                const $ = (sel, root = document) => root.querySelector(sel);

                function formatRp(n) {
                    const v = Math.round(Number(n) || 0);
                    return 'Rp ' + v.toLocaleString('id-ID');
                }

                function firstValidationMessage(data) {
                    if (!data.errors) return data.message || 'Terjadi kesalahan.';
                    const first = Object.values(data.errors)[0];
                    return Array.isArray(first) ? first[0] : String(first);
                }

                function openModal(el) { el.classList.remove('hidden'); el.classList.add('flex'); }
                function closeModal(el) { el.classList.add('hidden'); el.classList.remove('flex'); }

                window.__mitraDashboardData = {
                    menus: @json($mitraStatsMenusPayload),
                    stats: @json($stats),
                };
                const modalStatsOverview = document.getElementById('modal-mitra-stats-overview');

                function getMitraDashData() {
                    return window.__mitraDashboardData || { menus: [], stats: {} };
                }

                function renderStatsOverviewModal(focusKey) {
                    const { menus, stats } = getMitraDashData();
                    const s = stats && typeof stats === 'object' ? stats : {};
                    const totalBoxes = Number(s.total_boxes) || 0;
                    const totalStock = Number(s.total_stock) || 0;
                    const rev = Number(s.revenue_estimate) || 0;
                    const avgSav = Number(s.avg_savings) || 0;

                    document.getElementById('overview-recap-boxes').textContent = String(totalBoxes);
                    document.getElementById('overview-recap-stock').textContent = String(totalStock);
                    document.getElementById('overview-recap-revenue').textContent = formatRp(rev);
                    document.getElementById('overview-recap-avg').textContent = formatRp(avgSav);

                    const tbody = document.getElementById('mitra-stats-breakdown-tbody');
                    const foot = document.getElementById('mitra-stats-breakdown-foot');
                    tbody.replaceChildren();
                    foot.replaceChildren();

                    if (!menus.length) {
                        const tr = document.createElement('tr');
                        const td = document.createElement('td');
                        td.colSpan = 7;
                        td.className = 'px-4 py-8 text-center text-sm text-slate-500';
                        td.textContent = 'Belum ada mystery box. Tambahkan dari bagian “Mystery Box Saya”.';
                        tr.appendChild(td);
                        tbody.appendChild(tr);
                        return;
                    }

                    let sumStock = 0;
                    let sumSub = 0;
                    menus.forEach((m, idx) => {
                        const st = Number(m.stock) || 0;
                        const pr = Number(m.price) || 0;
                        const orig = Number(m.original_price) || 0;
                        const sub = Math.round(pr * st);
                        sumStock += st;
                        sumSub += sub;
                        const pct = typeof m.savings_percent === 'number' ? m.savings_percent : 0;

                        const tr = document.createElement('tr');
                        tr.className = 'border-b border-slate-100';

                        const td0 = document.createElement('td');
                        td0.className = 'px-3 py-2 text-slate-500';
                        td0.textContent = String(idx + 1);
                        tr.appendChild(td0);

                        const td1 = document.createElement('td');
                        td1.className = 'px-3 py-2 font-medium text-slate-900';
                        td1.textContent = m.name || '—';
                        tr.appendChild(td1);

                        const td2 = document.createElement('td');
                        td2.className = 'px-3 py-2 text-right tabular-nums';
                        td2.textContent = String(st);
                        tr.appendChild(td2);

                        const td3 = document.createElement('td');
                        td3.className = 'px-3 py-2 text-right tabular-nums';
                        td3.textContent = formatRp(pr);
                        tr.appendChild(td3);

                        const td4 = document.createElement('td');
                        td4.className = 'px-3 py-2 text-right tabular-nums text-slate-600';
                        td4.textContent = formatRp(orig);
                        tr.appendChild(td4);

                        const td5 = document.createElement('td');
                        td5.className = 'px-3 py-2 text-right tabular-nums text-orange-600';
                        td5.textContent = pct + '%';
                        tr.appendChild(td5);

                        const td6 = document.createElement('td');
                        td6.className = 'px-3 py-2 text-right font-semibold tabular-nums text-emerald-700';
                        td6.textContent = formatRp(sub);
                        tr.appendChild(td6);

                        tbody.appendChild(tr);
                    });

                    const trf = document.createElement('tr');
                    const tdf0 = document.createElement('td');
                    tdf0.colSpan = 2;
                    tdf0.className = 'px-3 py-3 font-bold text-slate-800';
                    tdf0.textContent = 'Total';
                    trf.appendChild(tdf0);
                    const tdfStock = document.createElement('td');
                    tdfStock.className = 'px-3 py-3 text-right font-bold tabular-nums text-slate-900';
                    tdfStock.textContent = String(sumStock);
                    trf.appendChild(tdfStock);
                    const tdfMid = document.createElement('td');
                    tdfMid.colSpan = 3;
                    tdfMid.className = 'px-3 py-3 text-right text-slate-400';
                    tdfMid.textContent = '';
                    trf.appendChild(tdfMid);
                    const tdfSub = document.createElement('td');
                    tdfSub.className = 'px-3 py-3 text-right font-black tabular-nums text-emerald-700';
                    tdfSub.textContent = formatRp(sumSub);
                    trf.appendChild(tdfSub);
                    foot.appendChild(trf);
                }

                function openStatsOverview(focusKey) {
                    renderStatsOverviewModal(focusKey);
                    openModal(modalStatsOverview);
                    const map = {
                        boxes: 'stat-section-boxes',
                        stock: 'stat-section-stock',
                        revenue: 'stat-section-revenue',
                        avg: 'stat-section-avg',
                    };
                    const id = map[focusKey] || 'stat-section-boxes';
                    requestAnimationFrame(() => {
                        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    });
                }

                document.getElementById('mitra-stats')?.addEventListener('click', (e) => {
                    const card = e.target.closest('[data-stat-card]');
                    if (!card) return;
                    openStatsOverview(card.getAttribute('data-stat-card') || 'boxes');
                });
                document.getElementById('mitra-stats')?.addEventListener('keydown', (e) => {
                    if (e.key !== 'Enter' && e.key !== ' ') return;
                    const card = e.target.closest('[data-stat-card]');
                    if (!card) return;
                    e.preventDefault();
                    openStatsOverview(card.getAttribute('data-stat-card') || 'boxes');
                });

                document.querySelectorAll('.modal-close').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const p = btn.closest('.fixed');
                        if (p) closeModal(p);
                    });
                });

                const modalCreate = $('#modal-mystery-create');
                const btnOpenCreate = $('#btn-open-mystery-create');
                const formCreate = $('#form-mystery-create');

                btnOpenCreate?.addEventListener('click', () => openModal(modalCreate));
                document.querySelectorAll('.btn-open-mystery-create').forEach(b => b.addEventListener('click', () => openModal(modalCreate)));

                formCreate?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const fd = new FormData(formCreate);
                    const body = Object.fromEntries(fd.entries());
                    body.price = Number(body.price);
                    body.original_price = body.original_price === '' ? 0 : Number(body.original_price);
                    body.stock = Number(body.stock);

                    const res = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(body),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        alert(firstValidationMessage(data));
                        return;
                    }
                    window.refreshMitraDashboard?.();
                    closeModal(modalCreate);
                    formCreate.reset();
                });

                const modalDetail = $('#modal-mystery-detail');

                function savingsPct(m) {
                    if (typeof m.savings_percent === 'number' && !Number.isNaN(m.savings_percent)) {
                        return m.savings_percent;
                    }
                    const o = Number(m.original_price) || 0;
                    const p = Number(m.price) || 0;
                    if (o <= 0) return 0;
                    return Math.round((1 - p / o) * 100);
                }

                function openDetail(m) {
                    const pct = savingsPct(m);
                    $('#detail-img').src = m.image_url || placeholderImg;
                    $('#detail-img').alt = m.name || '';
                    $('#detail-name').textContent = m.name || '';
                    const catEl = $('#detail-category');
                    if (m.category) {
                        catEl.textContent = m.category;
                        catEl.classList.remove('hidden');
                    } else {
                        catEl.textContent = '';
                        catEl.classList.add('hidden');
                    }
                    $('#detail-description').textContent = m.description || m.category || '—';
                    $('#detail-price').textContent = formatRp(m.price);
                    $('#detail-original').textContent = formatRp(m.original_price);
                    $('#detail-hemat').textContent = pct + '%';
                    $('#detail-stock').textContent = String(m.stock ?? 0) + ' unit';
                    $('#detail-pickup').textContent = m.pickup_time || '—';
                    const link = $('#detail-public-link');
                    link.href = publicBoxUrl(m.id);
                    openModal(modalDetail);
                }

                const formEdit = $('#form-mystery-edit');
                const modalEdit = $('#modal-mystery-edit');

                function openEdit(m) {
                    $('#edit-menu-id').value = m.id;
                    $('#edit-name').value = m.name;
                    $('#edit-price').value = m.price;
                    $('#edit-original_price').value = m.original_price;
                    $('#edit-category').value = m.category || '';
                    $('#edit-description').value = m.description || '';
                    $('#edit-stock').value = m.stock;
                    $('#edit-pickup_time').value = m.pickup_time || '';
                    $('#edit-image_url').value = m.image_url || '';
                    openModal(modalEdit);
                }

                document.getElementById('mystery-grid')?.addEventListener('click', (e) => {
                    if (e.target.closest('.btn-edit-mystery')) {
                        e.stopPropagation();
                        const editBtn = e.target.closest('.btn-edit-mystery');
                        const card = editBtn?.closest('.mystery-card');
                        if (!card?.dataset.menu) return;
                        try {
                            openEdit(JSON.parse(card.dataset.menu));
                        } catch (err) {
                            console.error(err);
                        }
                        return;
                    }
                    if (e.target.closest('.btn-delete-mystery')) {
                        e.stopPropagation();
                        const delBtn = e.target.closest('.btn-delete-mystery');
                        const card = delBtn?.closest('.mystery-card');
                        if (!card?.dataset.menu) return;
                        try {
                            const m = JSON.parse(card.dataset.menu);
                            deleteTarget = { id: m.id };
                            $('#delete-item-title').textContent = m.name;
                            $('#delete-item-price').textContent = formatRp(m.price);
                            $('#delete-preview-img').src = m.image_url || placeholderImg;
                            openModal($('#modal-mystery-delete'));
                        } catch (err) {
                            console.error(err);
                        }
                        return;
                    }
                    const card = e.target.closest('.mystery-card');
                    if (!card?.dataset.menu) return;
                    try {
                        openDetail(JSON.parse(card.dataset.menu));
                    } catch (err) {
                        console.error(err);
                    }
                });

                let deleteTarget = { id: null };

                formEdit?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const id = $('#edit-menu-id').value;
                    const body = {
                        name: $('#edit-name').value,
                        price: Number($('#edit-price').value),
                        original_price: $('#edit-original_price').value === '' ? 0 : Number($('#edit-original_price').value),
                        category: $('#edit-category').value || null,
                        description: $('#edit-description').value || null,
                        stock: Number($('#edit-stock').value),
                        pickup_time: $('#edit-pickup_time').value || null,
                        image_url: $('#edit-image_url').value || null,
                    };
                    const res = await fetch(updateUrl(id), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(body),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        alert(firstValidationMessage(data));
                        return;
                    }
                    window.refreshMitraDashboard?.();
                    closeModal(modalEdit);
                });

                $('#btn-confirm-delete')?.addEventListener('click', async () => {
                    if (!deleteTarget.id) return;
                    const res = await fetch(updateUrl(deleteTarget.id), {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        alert(firstValidationMessage(data));
                        return;
                    }
                    window.refreshMitraDashboard?.();
                    closeModal($('#modal-mystery-delete'));
                    deleteTarget = { id: null };
                });
            })();
        </script>
    @endif
</x-layouts.app>
