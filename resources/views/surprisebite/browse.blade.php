@php
    $browseQuery = static fn (array $overrides = []) => array_merge([
        'ft' => $filterType,
        'max_price' => $maxPrice,
        'sort' => $sort,
        'q' => $q ?? '',
    ], $overrides);
    $sbOosDefaultMsg = 'Menu tidak dapat ditambahkan karena stok tidak tersedia.';
@endphp

@push('body-scripts')
    <script>
        (function () {
            if (typeof globalThis !== 'undefined' && globalThis.__sbStockOosNotifyBound === true) {
                return;
            }
            if (typeof globalThis !== 'undefined') {
                globalThis.__sbStockOosNotifyBound = true;
            }

            var DEFAULT = @json($sbOosDefaultMsg);

            function showSbOosToast(text) {
                var msg = text && String(text).trim().length ? String(text).trim() : DEFAULT;
                var prev = document.getElementById('sb-stock-toast');
                if (prev) {
                    prev.remove();
                }

                var el = document.createElement('div');
                el.id = 'sb-stock-toast';
                el.setAttribute('role', 'alert');
                el.textContent = msg;
                el.style.cssText =
                    'position:fixed;bottom:1.5rem;left:50%;z-index:99999;max-width:min(calc(100vw - 2rem),24rem);padding:0.875rem 1rem;border-radius:1rem;border:2px solid #fcd34d;background:#fffbeb;color:#78350f;font-size:0.875rem;font-weight:700;box-shadow:0 10px 25px rgba(0,0,0,0.12);opacity:0;transform:translate(-50%,8px);transition:opacity .25s ease,transform .25s ease;pointer-events:none;text-align:center;line-height:1.45;';
                document.body.appendChild(el);

                requestAnimationFrame(function () {
                    el.style.opacity = '1';
                    el.style.transform = 'translate(-50%,0)';
                });

                setTimeout(function () {
                    el.style.opacity = '0';
                    el.style.transform = 'translate(-50%,8px)';
                    setTimeout(function () {
                        el.remove();
                    }, 300);
                }, 4200);
            }

            document.addEventListener(
                'click',
                function (e) {
                    var btn = e.target && e.target.closest && e.target.closest('[data-sb-oos-notify]');
                    if (!btn) {
                        return;
                    }
                    e.preventDefault();
                    e.stopPropagation();
                    var raw = btn.getAttribute('data-sb-oos-message');
                    showSbOosToast(raw || DEFAULT);
                },
                true,
            );
        })();
    </script>
@endpush

<x-layouts.app :title="'Browse Mystery Boxes • SurpriseBite'" variant="marketing" active-nav="browse">
    <div class="pb-16 pt-6 sm:pt-8" data-browse-live data-catalog-hash="{{ $catalogHash ?? '' }}">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100" role="status">
                {{ session('status') }}
            </div>
        @endif
        <header class="text-center sm:text-left">
            <h1 class="flex flex-wrap items-center justify-center gap-2 text-3xl font-black tracking-tight text-[#1e2939] sm:justify-start sm:text-4xl md:text-5xl">
                Browse <span class="text-[#00a63e]">Mystery Boxes</span>
                <x-sb.icon name="gift" class="h-8 w-8 shrink-0 text-[#ff6900] sm:h-10 sm:w-10" />
            </h1>
            <p class="mt-2 text-base text-[#6a7282] sm:text-lg">Temukan kejutan makanan terbaik untukmu!</p>
        </header>

        <section class="mt-8 rounded-3xl bg-white p-5 shadow-md shadow-black/5 ring-1 ring-slate-100 sm:p-8">
            <h2 class="flex items-center gap-2 text-lg font-black text-[#1e2939]">
                <x-sb.icon name="funnel" class="h-5 w-5 text-[#00a63e]" /> Filters
            </h2>

            <form method="get" action="{{ route('browse') }}" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-stretch">
                <input type="hidden" name="ft" value="{{ $filterType }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="max_price" value="{{ $maxPrice }}">
                <label for="browse-search-q" class="sr-only">Cari mystery box atau restoran</label>
                <input
                    id="browse-search-q"
                    type="search"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Cari nama box, restoran, atau jenis makanan…"
                    class="min-h-[3.25rem] w-full flex-1 rounded-2xl border-2 border-slate-200 bg-[#fafafa] px-4 py-3 text-base font-semibold text-[#1e2939] placeholder:text-[#6a7282] ring-0 transition focus:border-[#00a63e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#00a63e]/15"
                    autocomplete="off"
                >
                <button type="submit" class="inline-flex min-h-[3.25rem] shrink-0 items-center justify-center gap-2 rounded-2xl bg-[#00a63e] px-8 py-3 text-base font-black text-white shadow-lg transition hover:bg-[#008f36] sm:px-10">
                    <x-sb.icon name="search" class="h-6 w-6" /> Cari
                </button>
            </form>

            <div class="mt-6">
                <p class="text-sm font-bold text-[#1e2939]">Jenis Makanan</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($filterLabels as $key => $label)
                        <a href="{{ route('browse', $browseQuery(['ft' => $key])) }}"
                           class="rounded-full px-4 py-2 text-sm font-bold transition {{ $filterType === $key ? 'bg-[#00a63e] text-white shadow-md shadow-emerald-900/15' : 'bg-[#f3f4f6] text-[#364153] ring-1 ring-slate-200/80 hover:bg-[#e5e7eb]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <form method="get" action="{{ route('browse') }}" class="mt-8 space-y-2" id="browse-price-form">
                <input type="hidden" name="ft" value="{{ $filterType }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="q" value="{{ $q ?? '' }}">
                <div class="flex items-end justify-between gap-4">
                    <p class="text-sm font-bold text-[#1e2939]">Harga Maksimal</p>
                    <p class="text-lg font-black text-[#00a63e]">{{ $money($maxPrice) }}</p>
                </div>
                <input type="range" name="max_price" min="10000" max="50000" step="5000" value="{{ $maxPrice }}"
                       class="mt-2 h-2 w-full cursor-pointer appearance-none rounded-full bg-[#dcfce7] accent-[#00a63e]"
                       onchange="document.getElementById('browse-price-form').submit()">
                <div class="flex justify-between text-xs font-semibold text-[#6a7282]">
                    <span>{{ $money(10000) }}</span>
                    <span>{{ $money(50000) }}</span>
                </div>
            </form>

            <div class="mt-8">
                <p class="text-sm font-bold text-[#1e2939]">Urutkan</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('browse', $browseQuery(['sort' => 'nearest'])) }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-bold transition {{ $sort === 'nearest' ? 'bg-gradient-to-r from-[#ff8904] to-[#f54900] text-white shadow-md' : 'bg-[#f3f4f6] text-[#364153] ring-1 ring-slate-200/80 hover:bg-[#e5e7eb]' }}">
                        <x-sb.icon name="map-pin" class="h-4 w-4 {{ $sort === 'nearest' ? 'text-white' : 'text-[#00a63e]' }}" /> Terdekat
                    </a>
                    <a href="{{ route('browse', $browseQuery(['sort' => 'price'])) }}"
                       class="rounded-full px-4 py-2 text-sm font-bold transition {{ $sort === 'price' ? 'bg-gradient-to-r from-[#ff8904] to-[#f54900] text-white shadow-md' : 'bg-[#f3f4f6] text-[#364153] ring-1 ring-slate-200/80 hover:bg-[#e5e7eb]' }}">
                        Harga
                    </a>
                    <a href="{{ route('browse', $browseQuery(['sort' => 'rating'])) }}"
                       class="rounded-full px-4 py-2 text-sm font-bold transition {{ $sort === 'rating' ? 'bg-gradient-to-r from-[#ff8904] to-[#f54900] text-white shadow-md' : 'bg-[#f3f4f6] text-[#364153] ring-1 ring-slate-200/80 hover:bg-[#e5e7eb]' }}">
                        Rating
                    </a>
                </div>
            </div>
        </section>

        <div class="mt-8 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="flex items-center gap-2 text-sm font-semibold text-[#364153] sm:text-base">
                <x-sb.icon name="search" class="h-5 w-5 shrink-0 text-[#00a63e]" aria-hidden="true" />
                Menampilkan <span class="font-black text-[#00a63e]">{{ count($boxes) }}</span> mystery box
            </p>
            <p class="flex items-center justify-end gap-1.5 text-sm font-bold text-[#ff6900]">
                <x-sb.icon name="sparkles" class="h-4 w-4 shrink-0" /> Jangan sampai kehabisan!
            </p>
        </div>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($boxes as $box)
                @php
                    $r = $restaurant_lookup[(string) ($box['restaurant_id'] ?? '')] ?? null;
                    $orig = (float) ($box['original_price'] ?? 0);
                    $pct = $orig > 0 ? (int) round(100 - ((float) ($box['price'] ?? 0) / $orig) * 100) : 0;
                    $menuWishlisted = in_array($box['slug'], $wishlistMenuSlugs ?? [], true);
                    $stk = (int) ($box['stock'] ?? 0);
                    $oosNotice = $sbOosDefaultMsg;
                @endphp
                <article @class([
                    'relative overflow-hidden rounded-3xl bg-white shadow-md shadow-black/5 ring-1 ring-slate-100 transition hover:shadow-lg',
                    'opacity-[0.92]' => $stk <= 0,
                ])
                    data-sb-catalog-card
                    data-sb-box-slug="{{ $box['slug'] }}"
                >
                    <div class="relative aspect-[4/3] overflow-hidden">
                        <div class="absolute left-3 top-3 z-20">
                            <x-wishlist.heart type="menu" :target-key="$box['slug']" :active="$menuWishlisted" class="!h-9 !w-9" />
                        </div>
                        <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}" class="relative block h-full">
                            <img src="{{ $box['image'] }}" alt="" class="h-full w-full object-cover">
                            @if ($stk > 0)
                                @if ($stk <= 3)
                                    <span class="absolute left-3 top-14 z-10 rounded-full bg-red-600 px-2.5 py-1 text-xs font-black text-white shadow">
                                        Sisa {{ $stk }}
                                    </span>
                                @else
                                    <span class="absolute left-3 top-14 z-10 rounded-full bg-emerald-600 px-2.5 py-1 text-xs font-black text-white shadow">
                                        Stok: {{ $stk }}
                                    </span>
                                @endif
                            @else
                                <span class="absolute left-3 top-14 z-10 rounded-full bg-slate-700 px-2.5 py-1 text-xs font-black text-white shadow">
                                    Stok habis
                                </span>
                            @endif
                            <span class="absolute right-3 top-3 rounded-full bg-gradient-to-r from-[#ff8904] to-[#f54900] px-2.5 py-1 text-xs font-black text-white shadow">
                                -{{ $pct }}%
                            </span>
                            <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-xs font-bold text-[#1e2939] shadow ring-1 ring-black/5">
                                <x-sb.icon name="star" class="h-3.5 w-3.5 text-amber-500" /> {{ number_format((float) ($box['card_rating'] ?? 0), 1) }}@if ((int) ($box['ratings_count'] ?? 0) > 0)<span class="text-[10px] font-semibold text-[#6a7282]">({{ $box['ratings_count'] }})</span>@endif
                            </span>
                        </a>
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-semibold text-[#6a7282]">{{ $box['category_label'] ?? '' }} • <span class="inline-flex items-center gap-1" role="presentation"><x-sb.icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-[#6a7282]" aria-hidden="true" /> {{ number_format((float) ($box['distance_km'] ?? 0), 1) }} km</span></p>
                        <h3 class="mt-1 text-lg font-black text-[#1e2939]">{{ $box['title'] }}</h3>
                        <p class="mt-0.5 text-sm text-[#6a7282]">{{ data_get($r, 'name') }}</p>
                        <p class="mt-2 text-xs font-bold {{ $stk > 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                            @if ($stk > 0)
                                Stok tersedia: {{ $stk }}
                            @else
                                Tidak tersedia (stok habis)
                            @endif
                        </p>
                        <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-[#fff7ed] px-3 py-1 text-xs font-bold text-[#c2410c] ring-1 ring-[#fed7aa]">
                            <x-sb.icon name="clock" class="h-3.5 w-3.5 shrink-0" aria-hidden="true" /> {{ $box['pickup_time'] }}
                        </p>
                        <div class="mt-4 flex items-end justify-between gap-3">
                            <div>
                                <p class="text-sm text-[#9ca3af] line-through">{{ $money($box['original_price']) }}</p>
                                <p class="text-xl font-black text-[#00a63e]">{{ $money($box['price']) }}</p>
                            </div>
                            <div class="relative z-30 flex shrink-0 gap-2">
                                @if ($stk > 0)
                                    <form action="{{ route('cart.add') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="box_slug" value="{{ $box['slug'] }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button
                                            type="submit"
                                            class="relative z-30 inline-flex shrink-0 items-center gap-1.5 rounded-full bg-blue-500 px-3 py-2 text-xs font-black text-white shadow-md transition hover:bg-blue-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/55 focus-visible:ring-offset-2"
                                            title="Tambahkan ke keranjang"
                                            aria-label="{{ $box['title'] }} — tambah satu ke keranjang"
                                            data-sb-add-to-cart
                                        >
                                            <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </form>
                                    <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}"
                                       class="relative z-30 inline-flex shrink-0 items-center gap-1.5 rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-4 py-2.5 text-sm font-black text-white shadow-md shadow-emerald-900/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#008236]/50 focus-visible:ring-offset-2"
                                       data-sb-grab-it
                                       data-sb-target="box-detail"
                                       aria-label="{{ $box['title'] }} — Grab It, buka detail mystery box">
                                        Grab It! <x-sb.icon name="package" class="h-4 w-4 shrink-0 text-white" aria-hidden="true" />
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        data-sb-oos-notify
                                        data-sb-oos-message="{{ e($oosNotice) }}"
                                        class="relative z-30 inline-flex min-h-[40px] min-w-[40px] shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-full border-2 border-blue-600 bg-blue-600 px-3 py-2 text-xs font-black text-white shadow-md transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                        style="min-height:40px;min-width:40px;box-sizing:border-box;background-color:#2563eb;color:#fff;border:2px solid #1d4ed8;border-radius:9999px;padding:0.5rem 0.75rem;font-size:0.75rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;gap:0.375rem;cursor:pointer;"
                                        title="{{ $oosNotice }}"
                                        aria-label="{{ $oosNotice }}"
                                    >
                                        <svg class="h-4 w-4 shrink-0" style="color:#fff" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        data-sb-oos-notify
                                        data-sb-oos-message="{{ e($oosNotice) }}"
                                        class="relative z-30 inline-flex min-h-[44px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-slate-600 bg-gradient-to-r from-slate-500 to-slate-600 px-4 py-2.5 text-sm font-black text-white shadow-md transition hover:from-slate-600 hover:to-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2"
                                        style="min-height:44px;box-sizing:border-box;background:linear-gradient(to right,#64748b,#475569);color:#fff;border:2px solid #334155;border-radius:9999px;padding:0.625rem 1rem;font-size:0.875rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;cursor:pointer;"
                                        aria-label="{{ $oosNotice }}"
                                    >
                                        <span style="color:#fff">Grab It!</span>
                                        <svg class="h-4 w-4 shrink-0" style="color:#fff" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 16-9 5-9-5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 8 9 5 9-5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8v8l9 5 9-5V8"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 3 8l9 5 9-5-9-5Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v10"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @auth
                            @if(auth()->user()->role === 'customer' && isset(($pendingRatingBySlug ?? [])[$box['slug']]))
                                <x-browse.pending-rating :public-order-id="$pendingRatingBySlug[$box['slug']]" class="mt-3" />
                            @endif
                        @endauth
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl bg-white py-16 text-center text-[#6a7282] ring-1 ring-slate-100">
                    <p class="font-bold text-[#1e2939]">Tidak ada box yang cocok</p>
                    <p class="mt-2 text-sm">Coba ubah filter atau naikkan harga maksimal.</p>
                    <a href="{{ route('browse', ['ft' => 'all', 'max_price' => 50000, 'sort' => 'nearest']) }}" class="mt-4 inline-block font-bold text-[#00a63e] hover:underline">Reset filter</a>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
