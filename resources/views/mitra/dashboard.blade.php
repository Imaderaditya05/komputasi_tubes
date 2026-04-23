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
                <div
                    id="mitra-live-root"
                    data-mitra-dashboard-live
                    data-mitra-fingerprint="{{ $mitraLiveHash }}"
                    data-mitra-live-url="{{ route('mitra.api.live.restaurant', $restaurant) }}"
                    data-placeholder-img="{{ $placeholderImg }}"
                >
                <!-- Statistik -->
                <div class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" id="mitra-stats">
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Mystery Box</p>
                                <p class="stat-total-boxes text-3xl font-black text-emerald-600" data-value="{{ $stats['total_boxes'] }}">{{ $stats['total_boxes'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Stok</p>
                                <p class="stat-total-stock text-3xl font-black text-blue-600" data-value="{{ $stats['total_stock'] }}">{{ $stats['total_stock'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Estimasi Revenue</p>
                                <p class="stat-revenue text-2xl font-black text-orange-600 sm:text-3xl" data-value="{{ $stats['revenue_estimate'] }}">{{ $rp($stats['revenue_estimate']) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Avg. Savings</p>
                                <p class="stat-avg-savings text-2xl font-black text-violet-600 sm:text-3xl" data-value="{{ $stats['avg_savings'] }}">{{ $rp($stats['avg_savings']) }}</p>
                            </div>
                        </div>
                    </div>
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

                const modalEdit = $('#modal-mystery-edit');
                const formEdit = $('#form-mystery-edit');

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
                    const editBtn = e.target.closest('.btn-edit-mystery');
                    if (editBtn) {
                        const card = editBtn.closest('.mystery-card');
                        if (!card?.dataset.menu) return;
                        try {
                            openEdit(JSON.parse(card.dataset.menu));
                        } catch (err) {
                            console.error(err);
                        }
                    }
                    const delBtn = e.target.closest('.btn-delete-mystery');
                    if (delBtn) {
                        const card = delBtn.closest('.mystery-card');
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
