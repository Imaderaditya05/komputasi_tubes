@php
    $browseQuery = static fn (array $overrides = []) => array_merge([
        'ft' => $filterType,
        'max_price' => $maxPrice,
        'sort' => $sort,
        'q' => $q ?? '',
    ], $overrides);
@endphp

<x-layouts.app :title="'Browse Mystery Boxes • SurpriseBite'" variant="marketing" active-nav="browse">
    <div class="pb-16 pt-6 sm:pt-8" data-browse-live data-catalog-hash="{{ $catalogHash ?? '' }}">
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
                @endphp
                <article class="overflow-hidden rounded-3xl bg-white shadow-md shadow-black/5 ring-1 ring-slate-100 transition hover:shadow-lg">
                    <div class="relative aspect-[4/3] overflow-hidden">
                        <div class="absolute left-3 top-3 z-20">
                            <x-wishlist.heart type="menu" :target-key="$box['slug']" :active="$menuWishlisted" class="!h-9 !w-9" />
                        </div>
                        <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}" class="relative block h-full">
                            <img src="{{ $box['image'] }}" alt="" class="h-full w-full object-cover">
                            @if ($box['stock'] <= 3)
                                <span class="absolute left-3 top-14 rounded-full bg-red-600 px-2.5 py-1 text-xs font-black text-white shadow">
                                    Only {{ $box['stock'] }} left!
                                </span>
                            @endif
                            <span class="absolute right-3 top-3 rounded-full bg-gradient-to-r from-[#ff8904] to-[#f54900] px-2.5 py-1 text-xs font-black text-white shadow">
                                -{{ $pct }}%
                            </span>
                            <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-xs font-bold text-[#1e2939] shadow ring-1 ring-black/5">
                                <x-sb.icon name="star" class="h-3.5 w-3.5 text-amber-500" /> {{ number_format((float) ($box['card_rating'] ?? 0), 1) }}
                            </span>
                        </a>
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-semibold text-[#6a7282]">{{ $box['category_label'] ?? '' }} • {{ number_format((float) ($box['distance_km'] ?? 0), 1) }} km</p>
                        <h3 class="mt-1 text-lg font-black text-[#1e2939]">{{ $box['title'] }}</h3>
                        <p class="mt-0.5 text-sm text-[#6a7282]">{{ data_get($r, 'name') }}</p>
                        <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-[#fff7ed] px-3 py-1 text-xs font-bold text-[#c2410c] ring-1 ring-[#fed7aa]">
                            <x-sb.icon name="clock" class="h-3.5 w-3.5 shrink-0" /> {{ $box['pickup_time'] }}
                        </p>
                        <div class="mt-4 flex items-end justify-between gap-3">
                            <div>
                                <p class="text-sm text-[#9ca3af] line-through">{{ $money($box['original_price']) }}</p>
                                <p class="text-xl font-black text-[#00a63e]">{{ $money($box['price']) }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('cart.add') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="box_slug" value="{{ $box['slug'] }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-blue-500 hover:bg-blue-600 px-3 py-2 text-xs font-black text-white shadow-md transition" title="Add to Cart">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </form>
                                <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}"
                                   class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-4 py-2.5 text-sm font-black text-white shadow-md shadow-emerald-900/15">
                                    Grab It! <x-sb.icon name="package" class="h-4 w-4 text-white" />
                                </a>
                            </div>
                        </div>
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
