<x-layouts.admin title="Restaurant Management" active="restaurants">
    <div class="rounded-[24px] border-2 border-[#f3f4f6] bg-white p-6 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] sm:p-8"
         style="background-image: linear-gradient(141.254deg, rgb(249, 250, 251) 0%, rgba(255, 247, 237, 0.35) 100%);">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-base font-bold text-[#4a5565] hover:text-[#f97316]">
            <span class="text-lg" aria-hidden="true">←</span>
            Back to Admin Dashboard
        </a>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-[#1e2939] sm:text-4xl">Restaurant Management</h2>
                <p class="mt-1 text-base font-semibold text-[#4a5565]">Panel pengelolaan SurpriseBite</p>
            </div>
            <button type="button" onclick="document.getElementById('dlg-add').showModal()"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#f97316] to-[#ea580c] px-6 py-3 text-base font-black text-white shadow-lg hover:opacity-95">
                <span aria-hidden="true">+</span> Add Restaurant
            </button>
        </div>

        <div class="mt-5 rounded-2xl border-2 border-blue-200 bg-blue-50 p-4 sm:p-5">
            <p class="text-sm font-bold text-blue-900">
                <span class="font-black">Alur Validasi Mitra:</span> 
                (1) Mitra mendaftar → muncul di "Persetujuan akun mitra baru" 
                (2) Setujui akun mitra → mitra dapat akses dashboard 
                (3) Mitra buat restoran → muncul di daftar restaurant dengan status "Pending" 
                (4) Validasi restoran mitra di kartu dengan tombol "Validasi"
            </p>
        </div>

        @php
            $pendingCustomerReports = $pendingCustomerReports ?? collect();
            $pendingAccessAppeals = $pendingAccessAppeals ?? collect();
            $pendingMitraUsers = $pendingMitraUsers ?? collect();
        @endphp
        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border-2 border-sky-200 bg-sky-50/90 p-5 shadow-sm sm:p-6">
                <h3 class="text-lg font-black text-[#1e2939]">Persetujuan akun mitra baru</h3>
                <p class="mt-1 text-sm font-semibold text-[#6a7282]">Mitra yang baru mendaftar menunggu persetujuan untuk akses dashboard.</p>
                @if ($pendingMitraUsers->isEmpty())
                    <p class="mt-4 rounded-2xl border border-sky-100/80 bg-white/80 px-4 py-6 text-center text-sm font-semibold text-[#6a7282]">Tidak ada pendaftaran mitra yang menunggu.</p>
                @else
                    <ul class="mt-4 max-h-[28rem] space-y-3 overflow-y-auto pr-1">
                        @foreach ($pendingMitraUsers as $mitraUser)
                            <li class="rounded-2xl border border-sky-100 bg-white p-4 text-sm shadow-sm">
                                <p class="font-black text-[#1e2939]">{{ $mitraUser->name }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6a7282]">{{ $mitraUser->email }}</p>
                                @if ($mitraUser->phone)
                                    <p class="mt-1 text-xs font-bold text-[#6a7282]">{{ $mitraUser->phone }}</p>
                                @endif
                                <p class="mt-2 text-xs font-semibold text-slate-500">Daftar: {{ $mitraUser->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                                <div class="mt-3 flex gap-2">
                                    <form action="{{ route('admin.mitra.approve', $mitraUser) }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="mitra_approval_status" value="approved">
                                        <button type="submit" class="w-full rounded-lg bg-sky-600 px-3 py-2 text-xs font-bold text-white hover:bg-sky-700 transition">
                                            ✓ Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.mitra.approve', $mitraUser) }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="mitra_approval_status" value="rejected">
                                        <button type="submit" class="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white hover:bg-red-700 transition">
                                            ✗ Tolak
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="rounded-2xl border-2 border-rose-200 bg-rose-50/90 p-5 shadow-sm sm:p-6">
                <h3 class="text-lg font-black text-[#1e2939]">Laporan pelanggan</h3>
                <p class="mt-1 text-sm font-semibold text-[#6a7282]">Keluhan atau laporan terhadap mitra/toko — tinjau lalu kunci akses di kartu mitra bila perlu.</p>
                @if ($pendingCustomerReports->isEmpty())
                    <p class="mt-4 rounded-2xl border border-rose-100/80 bg-white/80 px-4 py-6 text-center text-sm font-semibold text-[#6a7282]">Belum ada laporan yang menunggu tinjauan.</p>
                @else
                    <ul class="mt-4 max-h-[28rem] space-y-3 overflow-y-auto pr-1">
                        @foreach ($pendingCustomerReports as $rep)
                            <li class="rounded-2xl border border-rose-100 bg-white p-4 text-sm shadow-sm">
                                <p class="font-black text-[#1e2939]">{{ $rep->restaurant_display_name }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6a7282]">
                                    <span class="font-mono text-[11px] text-slate-500">{{ $rep->partner_key }}</span>
                                    @if ($rep->box_slug)
                                        · Box: {{ $rep->box_slug }}
                                    @endif
                                </p>
                                @if ($rep->category)
                                    <p class="mt-2 text-xs font-black uppercase tracking-wide text-rose-700">{{ $rep->category }}</p>
                                @endif
                                <p class="mt-1 text-xs font-bold text-[#6a7282]">{{ $rep->reporter?->name }} · {{ $rep->reporter?->email }}</p>
                                <p class="mt-3 max-h-32 overflow-y-auto whitespace-pre-wrap text-[#364153] leading-relaxed">{{ Str::limit($rep->message, 1200) }}</p>
                                <p class="mt-2 text-xs font-semibold text-slate-500">{{ $rep->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50/90 p-5 shadow-sm sm:p-6">
                <h3 class="text-lg font-black text-[#1e2939]">Pengajuan banding akses mitra</h3>
                <p class="mt-1 text-sm font-semibold text-[#6a7282]">Menunggu tinjauan — buka akses di kartu mitra jika disetujui.</p>
                @if ($pendingAccessAppeals->isEmpty())
                    <p class="mt-4 rounded-2xl border border-amber-100/80 bg-white/80 px-4 py-6 text-center text-sm font-semibold text-[#6a7282]">Tidak ada pengajuan banding yang menunggu.</p>
                @else
                    <ul class="mt-4 max-h-[28rem] space-y-3 overflow-y-auto pr-1">
                        @foreach ($pendingAccessAppeals as $ap)
                            <li class="rounded-2xl border border-amber-100 bg-white p-4 text-sm shadow-sm">
                                <p class="font-black text-[#1e2939]">{{ $ap->restaurant?->name ?? 'Restoran #'.$ap->restaurant_id }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6a7282]">{{ $ap->user?->name }} · {{ $ap->user?->email }}</p>
                                <p class="mt-3 max-h-32 overflow-y-auto whitespace-pre-wrap text-[#364153] leading-relaxed">{{ Str::limit($ap->message, 1200) }}</p>
                                <p class="mt-2 text-xs font-semibold text-slate-500">{{ $ap->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        @php
            $listingFilter = $listingFilter ?? 'all';
            $statHref = function (string $f) use ($q) {
                $query = array_filter([
                    'q' => $q !== '' ? $q : null,
                    'filter' => $f !== 'all' ? $f : null,
                ], fn ($v) => $v !== null && $v !== '');

                return route('admin.restaurants', $query).'#rt-restaurants-grid';
            };
            $statRing = fn (string $f) => $listingFilter === $f
                ? 'ring-2 ring-offset-2 ring-slate-900 shadow-lg'
                : 'ring-2 ring-transparent hover:ring-slate-200';
        @endphp

        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5" id="rt-stat-cards" role="navigation" aria-label="Ringkasan dan filter daftar">
            <a href="{{ $statHref('all') }}"
               class="stat-filter-card block rounded-2xl border-2 border-orange-100 bg-white px-5 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#f97316] focus-visible:ring-offset-2 {{ $statRing('all') }}"
               @if ($listingFilter === 'all') aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Total Mitra <span class="block text-[10px] font-semibold normal-case text-slate-400">Semua tampil</span></p>
                <p class="mt-2 text-3xl font-black text-[#f97316] sm:text-4xl" id="rt-rest-total">{{ number_format($stats['total_restaurants']) }}</p>
            </a>
            <a href="{{ $statHref('with_boxes') }}"
               class="stat-filter-card block rounded-2xl border-2 border-emerald-100 bg-white px-5 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#00a63e] focus-visible:ring-offset-2 {{ $statRing('with_boxes') }}"
               @if ($listingFilter === 'with_boxes') aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Total Mystery Boxes <span class="block text-[10px] font-semibold normal-case text-slate-400">Yang punya box</span></p>
                <p class="mt-2 text-3xl font-black text-[#00a63e] sm:text-4xl" id="rt-rest-boxes">{{ number_format($stats['total_boxes']) }}</p>
            </a>
            <a href="{{ $statHref('active') }}"
               class="stat-filter-card block rounded-2xl border-2 border-sky-100 bg-white px-5 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#0284c7] focus-visible:ring-offset-2 {{ $statRing('active') }}"
               @if ($listingFilter === 'active') aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Sah (Unlocked)</p>
                <p class="mt-2 text-3xl font-black text-[#0284c7] sm:text-4xl" id="rt-rest-active">{{ number_format($stats['active']) }}</p>
            </a>
            <a href="{{ $statHref('pending') }}"
               class="stat-filter-card block rounded-2xl border-2 border-amber-100 bg-white px-5 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#d97706] focus-visible:ring-offset-2 {{ $statRing('pending') }}"
               @if ($listingFilter === 'pending') aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Menunggu</p>
                <p class="mt-2 text-3xl font-black text-[#d97706] sm:text-4xl" id="rt-rest-pending">{{ number_format($stats['pending']) }}</p>
            </a>
            <a href="{{ $statHref('locked') }}"
               class="stat-filter-card block rounded-2xl border-2 border-red-100 bg-white px-5 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#dc2626] focus-visible:ring-offset-2 sm:col-span-2 xl:col-span-1 {{ $statRing('locked') }}"
               @if ($listingFilter === 'locked') aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Terkunci</p>
                <p class="mt-2 text-3xl font-black text-[#dc2626] sm:text-4xl" id="rt-rest-locked">{{ number_format($stats['locked'] ?? 0) }}</p>
            </a>
        </div>

        @if ($listingFilter !== 'all')
            @php
                $filterLabels = [
                    'with_boxes' => 'Memiliki minimal satu mystery box',
                    'active' => 'Status Sah (Unlocked)',
                    'pending' => 'Status menunggu',
                    'locked' => 'Status terkunci',
                ];
            @endphp
            <p class="mt-3 text-sm font-semibold text-slate-600">
                <span class="font-black text-slate-800">Detail:</span>
                {{ $filterLabels[$listingFilter] ?? $listingFilter }}
                —
                <a href="{{ $statHref('all') }}" class="font-bold text-[#f97316] underline decoration-[#f97316]/40 underline-offset-2 hover:no-underline">Tampilkan semua mitra</a>
            </p>
        @endif

        <form method="get" action="{{ route('admin.restaurants') }}" class="mt-8" id="form-restaurants-search">
            @if ($listingFilter !== 'all')
                <input type="hidden" name="filter" value="{{ $listingFilter }}" />
            @endif
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-sb.icon name="search" class="h-5 w-5" /></span>
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Cari nama, lokasi, alamat, atau owner…"
                       class="w-full rounded-[14px] border-2 border-[#e5e7eb] py-3 pl-12 pr-4 text-base font-semibold text-[#1e2939] placeholder:text-[#71717a]/70 focus:border-[#f97316] focus:outline-none focus:ring-2 focus:ring-[#f97316]/25" />
            </div>
        </form>

        <div id="rt-restaurants-fetch-banner" class="mt-6 hidden rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-900">
            Gagal memperbarui daftar otomatis. Periksa koneksi lalu muat ulang halaman jika perlu.
        </div>

        <div
            id="rt-restaurants-grid"
            class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3"
            data-admin-rest-base="{{ url('/admin/restaurants') }}"
        >
            @include('surprisebite.admin.partials.restaurants-cards', ['entries' => $entries, 'money' => $money])
        </div>
    </div>

    {{-- Toast stack --}}
    <div id="admin-mitra-toast" class="fixed bottom-6 left-1/2 z-[60] flex w-[min(100vw-2rem,24rem)] -translate-x-1/2 flex-col gap-2"></div>

    <dialog
        id="dlg-mitra-validasi"
        class="fixed left-1/2 top-1/2 z-50 m-0 max-h-[min(90vh,540px)] w-[min(100vw-2rem,26rem)] max-w-[calc(100vw-2rem)] -translate-x-1/2 -translate-y-1/2 rounded-[28px] border-2 border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-900/60"
    >
        <form id="form-mitra-validasi" class="flex max-h-[min(90vh,540px)] flex-col p-6">
            <h3 class="text-xl font-black text-slate-900">Validasi Mitra</h3>
            <p id="mitra-modal-subtitle" class="mt-1 text-sm font-semibold text-slate-500"></p>
            <input type="hidden" id="mitra-modal-restaurant-id" value="" />
            <input type="hidden" id="mitra-modal-patch-base" value="" />
            <input type="hidden" id="mitra-modal-card-key" value="" />
            <label class="mt-5 block text-sm font-bold text-slate-800" for="mitra-modal-status">Status Akses</label>
            <select id="mitra-modal-status" name="status" required
                    class="mt-2 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-[#f97316] focus:outline-none focus:ring-2 focus:ring-[#f97316]/20">
                <option value="active">Sah (Unlocked)</option>
                <option value="locked">Kunci Akses (Locked)</option>
                <option value="pending">Menunggu (Pending)</option>
            </select>
            <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                <button type="button" id="mitra-modal-cancel"
                        class="rounded-2xl bg-slate-200 px-5 py-2.5 text-sm font-black text-slate-800 hover:bg-slate-300/90">
                    Batal
                </button>
                <button type="submit" id="mitra-modal-submit"
                        class="rounded-2xl bg-gradient-to-r from-[#f97316] to-[#ea580c] px-5 py-2.5 text-sm font-black text-white shadow-md shadow-orange-500/25 hover:opacity-95">
                    Simpan
                </button>
            </div>
        </form>
    </dialog>

    <dialog
        id="dlg-mitra-reject-lock"
        class="fixed left-1/2 top-1/2 z-[52] m-0 w-[min(100vw-1.5rem,26rem)] max-w-[calc(100vw-2rem)] -translate-x-1/2 -translate-y-1/2 rounded-[1.25rem] border-2 border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-[1px]"
    >
        <div class="p-6 pb-4">
            <h3 class="text-xl font-black text-slate-900">Tolak / kunci akses</h3>
            <p class="mt-4 text-base font-semibold leading-relaxed text-[#4a5565]">
                Apakah Anda yakin ingin menolak / mengunci akses
                <span id="mitra-reject-name" class="font-black text-[#1e2939]"></span>?
                Mitra tidak dihapus — status menjadi
                <span class="font-black text-red-700">Kunci Akses (Locked)</span>.
            </p>
        </div>
        <div class="flex shrink-0 flex-row flex-wrap justify-end gap-3 border-t border-slate-100 p-4">
            <button type="button" data-close-mitra-reject class="rounded-xl bg-slate-200 px-5 py-3 text-sm font-black text-slate-800 hover:bg-slate-300">
                Batal
            </button>
            <button type="button" id="mitra-reject-confirm" class="rounded-xl bg-red-600 px-5 py-3 text-sm font-black text-white shadow-md hover:bg-red-700">
                Kunci akses
            </button>
        </div>
    </dialog>

    <dialog id="dlg-add" class="w-full max-w-lg rounded-3xl border-2 border-slate-200 p-0 shadow-2xl backdrop:bg-slate-900/40">
        <form method="post" action="{{ route('admin.restaurants.store') }}" class="p-6">
            @csrf
            <h3 class="text-xl font-black text-slate-900">Add Restaurant</h3>
            <div class="mt-4 space-y-3">
                <label class="block text-sm font-bold text-slate-700">Name
                    <input name="name" required class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Owner
                    <input name="owner_name" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" placeholder="Nama pemilik / kontak" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Location (area)
                    <input name="location" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Alamat lengkap (opsional)
                    <textarea name="address_line" rows="2" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold"></textarea>
                </label>
                <label class="block text-sm font-bold text-slate-700">Image URL
                    <input name="image_url" type="url" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Description
                    <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold"></textarea>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-sm font-bold text-slate-700">Rating
                        <input name="rating" type="number" step="0.1" min="0" max="5" value="4.5" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Reviews
                        <input name="reviews" type="number" min="0" value="0" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                </div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Mystery box (opsional)</p>
                <label class="block text-sm font-bold text-slate-700">Box title
                    <input name="box_title" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" placeholder="Contoh: Bakery Surprise Box" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Box price (IDR)
                    <input name="box_price" type="number" min="0" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" placeholder="25000" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Status akses
                    <select name="status" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold">
                        <option value="active">Sah (Unlocked)</option>
                        <option value="pending">Menunggu (Pending)</option>
                        <option value="locked">Kunci Akses (Locked)</option>
                    </select>
                </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('dlg-add').close()" class="rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-black text-slate-800">Cancel</button>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-[#f97316] to-[#ea580c] px-5 py-2.5 text-sm font-black text-white">Add Restaurant</button>
            </div>
        </form>
    </dialog>

    <dialog id="dlg-edit" class="w-full max-w-lg rounded-3xl border-2 border-slate-200 p-0 shadow-2xl backdrop:bg-slate-900/40">
        <form id="form-edit" method="post" class="p-6">
            @csrf
            @method('PUT')
            <h3 class="text-xl font-black text-slate-900">Edit Restaurant</h3>
            <div class="mt-4 space-y-3">
                <label class="block text-sm font-bold text-slate-700">Name
                    <input name="name" id="edit-name" required class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Owner
                    <input name="owner_name" id="edit-owner_name" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Location (area)
                    <input name="location" id="edit-location" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Alamat lengkap
                    <textarea name="address_line" id="edit-address_line" rows="2" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold"></textarea>
                </label>
                <label class="block text-sm font-bold text-slate-700">Image URL
                    <input name="image_url" id="edit-image_url" type="url" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Description
                    <textarea name="description" id="edit-description" rows="3" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 text-sm font-semibold"></textarea>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-sm font-bold text-slate-700">Rating
                        <input name="rating" id="edit-rating" type="number" step="0.1" min="0" max="5" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Reviews
                        <input name="reviews" id="edit-reviews" type="number" min="0" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                </div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Mystery box</p>
                <label class="block text-sm font-bold text-slate-700">Box title
                    <input name="box_title" id="edit-box_title" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Box price (IDR)
                    <input name="box_price" id="edit-box_price" type="number" min="0" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Status akses
                    <select name="status" id="edit-status" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold">
                        <option value="active">Sah (Unlocked)</option>
                        <option value="pending">Menunggu (Pending)</option>
                        <option value="locked">Kunci Akses (Locked)</option>
                    </select>
                </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('dlg-edit').close()" class="rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-black text-slate-800">Cancel</button>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-[#f97316] to-[#ea580c] px-5 py-2.5 text-sm font-black text-white">Save</button>
            </div>
        </form>
    </dialog>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (location.hash !== '#rt-restaurants-grid') return;
            var el = document.getElementById('rt-restaurants-grid');
            if (!el) return;
            window.requestAnimationFrame(function () {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</x-layouts.admin>
