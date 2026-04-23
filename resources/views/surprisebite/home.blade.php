<x-layouts.app :title="'SurpriseBite'" variant="marketing">
    @if (session('success'))
        <div class="mx-auto mt-4 max-w-4xl rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-bold text-emerald-900"
             role="status">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mx-auto mt-4 max-w-4xl rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-center text-sm font-bold text-red-900"
             role="alert">
            {{ session('error') }}
        </div>
    @endif
    {{-- Hero — Figma node 32:114 --}}
    <section class="sb-hero-sheen relative mt-4 overflow-hidden rounded-2xl shadow-2xl shadow-emerald-900/30 sm:mt-5 sm:rounded-3xl"
             style="background: linear-gradient(145deg, #022c22 0%, #006b29 55%, #c2410c 100%);">
        <div class="pointer-events-none absolute inset-0 overflow-hidden text-white/15">
            <span class="sb-float-slow absolute left-4 top-16 sm:left-10" style="animation-delay: 0s"><x-sb.icon name="bento-deco" class="h-20 w-20 sm:h-28 sm:w-28" /></span>
            <span class="sb-float-slow absolute right-6 top-24 sm:right-20" style="animation-delay: 0.5s"><x-sb.icon name="leaf" class="h-14 w-14 sm:h-20 sm:w-20" /></span>
            <span class="sb-float-slow absolute bottom-32 left-1/4" style="animation-delay: 1s"><x-sb.icon name="noodles" class="h-16 w-16 sm:h-24 sm:w-24" /></span>
            <span class="sb-float-slow absolute right-1/4 top-40" style="animation-delay: 0.3s"><x-sb.icon name="utensils" class="h-12 w-12 sm:h-16 sm:w-16" /></span>
            <span class="sb-float-slow absolute bottom-20 right-8" style="animation-delay: 0.8s"><x-sb.icon name="italian" class="h-24 w-24 sm:h-32 sm:w-32" /></span>
        </div>
        <div class="pointer-events-none absolute left-8 top-10 h-36 w-36 rounded-full bg-[#ff8904] opacity-40 blur-[64px]"></div>
        <div class="pointer-events-none absolute bottom-20 right-10 h-48 w-48 rounded-full bg-[#fdc700] opacity-30 blur-[64px]"></div>

        <div class="relative flex min-h-[520px] flex-col items-center px-4 pb-16 pt-10 sm:min-h-[600px] sm:px-8 sm:pb-20 sm:pt-12">
            <div class="relative mb-6 flex h-32 w-auto items-center justify-center sm:min-h-[160px] sm:h-40 sb-animate-scale">
                <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="SurpriseBite Logo" 
                     class="relative z-10 h-full w-auto cursor-pointer object-contain drop-shadow-[0_15px_25px_rgba(0,0,0,0.6)] transition-all duration-700 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:scale-110 active:scale-95" 
                     onclick="this.classList.toggle('rotate-[360deg]')" />
            </div>

            <h1 class="sb-animate-up sb-delay-1 text-center text-4xl font-black leading-tight text-white sm:text-5xl md:text-6xl lg:text-7xl">
                Save Food,
            </h1>
            <p class="sb-animate-up sb-delay-2 mt-1 bg-gradient-to-r from-[#fde020] via-[#ffb86a] to-[#ff8904] bg-clip-text text-center text-4xl font-black leading-tight text-transparent sm:text-5xl md:text-6xl lg:text-7xl">
                Get Surprise Meals!
            </p>
            <p class="sb-animate-up sb-delay-3 mx-auto mt-6 max-w-2xl text-center text-lg leading-relaxed text-[#f0fdf4] sm:text-xl md:text-2xl">
                Kurangi food waste, dapatkan makanan berkualitas dengan harga terjangkau.
            </p>
            <a href="{{ route('browse') }}"
               class="sb-animate-up sb-delay-4 sb-btn-shine sb-hover-lift sb-hover-lift--warm relative z-10 mt-10 inline-flex items-center gap-3 rounded-full bg-gradient-to-r from-[#ff6900] to-[#f54900] px-10 py-4 text-lg font-bold text-white shadow-[0_25px_50px_rgba(0,0,0,0.25)] active:scale-[0.98]">
                <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                </svg>
                Find Mystery Box
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    {{-- Our Impact Together — Figma 32:158 --}}
    <section id="impact" class="relative mt-0 overflow-hidden rounded-b-3xl bg-white py-16 sm:py-20">
        <div class="pointer-events-none absolute inset-0 opacity-50"
             style="background: linear-gradient(158.052deg, rgb(240, 253, 244) 0%, rgb(255, 247, 237) 100%);"></div>
        <div class="relative mx-auto w-full px-0">
            <div class="text-center sb-animate-up">
                <h2 class="text-3xl font-black text-[#1e2939] sm:text-4xl md:text-5xl">
                    Our Impact <span class="inline-flex items-center gap-2 text-[#00a63e]">Together <x-sb.icon name="globe" class="h-[1.1em] w-[1.1em] shrink-0" /></span>
                </h2>
                <p class="mt-3 text-lg text-[#4a5565] sm:text-xl">Lihat dampak nyata yang kita ciptakan!</p>
                <p class="mt-4">
                    <a href="{{ route('impact') }}" class="inline-flex items-center gap-1 text-base font-bold text-[#00a63e] underline decoration-2 underline-offset-4 transition hover:text-[#008236] hover:decoration-[#008236]">
                        Lihat Impact Tracker lengkap →
                    </a>
                </p>
            </div>
            <div class="mt-12 grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
                <div class="sb-animate-up sb-delay-1 sb-stat-shine sb-hover-stat relative overflow-hidden rounded-3xl p-7 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1)] sm:p-8"
                     style="background: linear-gradient(140.845deg, rgb(0, 201, 80) 0%, rgb(0, 153, 102) 100%);">
                    <span class="absolute right-4 top-4 text-white/10"><x-sb.icon name="bento-deco" class="h-24 w-24 sm:h-28 sm:w-28" /></span>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 text-white">
                        <x-sb.icon name="package" class="h-9 w-9" />
                    </div>
                    <p class="relative mt-6 text-4xl font-black tabular-nums text-white sm:text-5xl">
                        <span data-sb-count="{{ $impactMeals }}" data-sb-duration="1650">0</span>
                    </p>
                    <p class="relative mt-1 text-lg font-semibold text-white/90">Meals Saved</p>
                </div>
                <div class="sb-animate-up sb-delay-2 sb-stat-shine sb-hover-stat relative overflow-hidden rounded-3xl p-7 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1)] sm:p-8"
                     style="background: linear-gradient(135deg, #ff8904 0%, #f54900 100%);">
                    <span class="absolute right-2 top-6 text-white/10"><x-sb.icon name="trending-down" class="h-20 w-20 sm:h-24 sm:w-24" /></span>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 text-white">
                        <x-sb.icon name="chart-bar" class="h-9 w-9" />
                    </div>
                    <p class="relative mt-6 text-4xl font-black tabular-nums text-white sm:text-5xl">
                        <span data-sb-count="{{ $impactWasteValue }}" data-sb-decimals="{{ $impactWasteDecimals }}" data-sb-duration="1900">0</span><span class="ml-1 text-2xl font-black sm:text-3xl">{{ $impactWasteUnit === 'kg' ? 'kg' : 'ton' }}</span>
                    </p>
                    <p class="relative mt-1 text-lg font-semibold text-white/90">Food Waste Reduced</p>
                </div>
                <div class="sb-animate-up sb-delay-3 sb-stat-shine sb-hover-stat relative overflow-hidden rounded-3xl p-7 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1)] sm:col-span-2 sm:p-8 lg:col-span-1"
                     style="background: linear-gradient(140.845deg, rgb(0, 201, 80) 0%, rgb(0, 153, 102) 100%);">
                    <span class="absolute right-4 top-4 text-white/10"><x-sb.icon name="users" class="h-24 w-24 sm:h-28 sm:w-28" /></span>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 text-white">
                        <x-sb.icon name="user-smile" class="h-9 w-9" />
                    </div>
                    <p class="relative mt-6 text-4xl font-black tabular-nums text-white sm:text-5xl">
                        <span data-sb-count="{{ $impactActiveUsers }}" data-sb-duration="1650">0</span>
                    </p>
                    <p class="relative mt-1 text-lg font-semibold text-white/90">Happy Users</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Nearby Mystery Boxes — Figma 32:228 --}}
    <section class="relative bg-white py-14 sm:py-16" data-browse-live data-catalog-hash="{{ $catalogHash ?? '' }}">
        <div class="mx-auto w-full px-0">
            <div class="sb-reveal flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-3xl font-black sm:text-4xl md:text-5xl">
                        Nearby <span class="inline-flex flex-wrap items-center gap-2 text-[#00a63e]">Mystery Boxes <x-sb.icon name="package" class="h-[1em] w-[1em] shrink-0 sm:h-9 sm:w-9" /></span>
                    </h2>
                    <p class="mt-2 text-lg text-[#4a5565]">Ambil sebelum kehabisan!</p>
                </div>
                <a href="{{ route('browse') }}" class="sb-hover-header-btn inline-flex shrink-0 items-center gap-2 rounded-full bg-[#00a63e] px-6 py-3 text-base font-bold text-white shadow-md hover:bg-[#008f36]">
                    View All
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('home', array_filter(['q' => $q])) }}"
                   class="sb-hover-chip inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-bold ring-2 {{ $selectedCategory === '' ? 'bg-[#00a63e] text-white ring-[#00a63e]' : 'bg-white text-[#364153] ring-slate-200 hover:ring-emerald-200' }}">
                    Semua
                </a>
                @foreach ($categories as $cat)
                    <a href="{{ route('home', array_filter(['category' => $cat['id'], 'q' => $q])) }}"
                       class="sb-hover-chip inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-bold ring-2 {{ $selectedCategory === $cat['id'] ? 'bg-[#00a63e] text-white ring-[#00a63e]' : 'bg-white text-[#364153] ring-slate-200 hover:ring-emerald-200' }}">
                        <x-sb.icon :name="$cat['icon']" class="h-4 w-4" />{{ $cat['name'] }}
                    </a>
                @endforeach
            </div>

            <div class="sb-reveal-group mt-10 grid gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
                @forelse ($boxes as $box)
                    @php
                        $r = collect($restaurants)->firstWhere('id', $box['restaurant_id']);
                        $discount = max(0, (int) round((1 - ($box['price'] / max(1, $box['original_price']))) * 100));
                        $catLabel = $box['category_label'] ?? collect($categories)->firstWhere('id', $box['category'])['name'] ?? 'Box';
                        $cardRating = $box['card_rating'] ?? ($r['rating'] ?? 4.5);
                        $homeMenuWishlisted = in_array($box['slug'], $wishlistMenuSlugs ?? [], true);
                    @endphp
                    <article class="group sb-reveal sb-hover-lift overflow-hidden rounded-2xl bg-white shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)] ring-1 ring-black/5">
                        <div class="relative h-56 w-full overflow-hidden sm:h-60">
                            <div class="absolute left-3 top-3 z-20">
                                <x-wishlist.heart type="menu" :target-key="$box['slug']" :active="$homeMenuWishlisted" class="!h-9 !w-9" />
                            </div>
                            <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}" class="relative block h-full">
                                <img src="{{ $box['image'] }}" alt="" class="sb-img-zoom h-full w-full object-cover" loading="lazy" width="800" height="600" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-transparent"></div>
                                @if (($box['stock'] ?? 99) <= 3)
                                    <div class="absolute left-3 top-14 inline-flex items-center gap-1.5 rounded-full bg-[#e7000b] px-4 py-2 text-sm font-black text-white shadow-lg">
                                        <x-sb.icon name="flame" class="h-4 w-4 text-white" /> Only {{ $box['stock'] }} left!
                                    </div>
                                @endif
                                <div class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-[#ff6900] to-[#fb2c36] px-3 py-1.5 text-sm font-black text-white shadow-lg">
                                    <x-sb.icon name="tag" class="h-4 w-4 text-white" />
                                    -{{ $discount }}%
                                </div>
                                <div class="absolute bottom-3 left-3 flex items-center gap-1 rounded-full bg-white/95 px-3 py-1.5 text-sm font-bold text-[#0a0a0a] shadow-md">
                                    <x-sb.icon name="star" class="h-4 w-4 text-amber-500" /> {{ number_format($cardRating, 1) }}
                                </div>
                            </a>
                            </div>
                            <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}" class="block">
                            <div class="p-5">
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span class="rounded-full bg-[#dcfce7] px-2.5 py-1 font-semibold text-[#008236]">{{ $catLabel }}</span>
                                    <span class="text-[#6a7282]">•</span>
                                    <span class="flex items-center gap-1 text-[#6a7282]">
                                        <x-sb.icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-[#6a7282]" /> {{ number_format($box['distance_km'], 1) }} km
                                    </span>
                                </div>
                                <h3 class="mt-3 text-xl font-black text-[#1e2939]">{{ $box['title'] }}</h3>
                                <p class="mt-1 text-sm text-[#4a5565]">{{ $r['name'] ?? 'Restaurant' }}</p>
                                <div class="mt-4 flex items-center gap-2 rounded-xl border border-[#ffd6a8] bg-[#fff7ed] px-3 py-3">
                                    <x-sb.icon name="clock" class="h-5 w-5 shrink-0 text-[#c2410c]" />
                                    <span class="text-sm font-semibold text-[#364153]">{{ $box['pickup_time'] }}</span>
                                </div>
                                <div class="mt-5 flex items-center justify-between border-t-2 border-[#f3f4f6] pt-5">
                                    <div>
                                        <p class="text-xs font-medium text-[#99a1af] line-through">{{ $money($box['original_price']) }}</p>
                                        <p class="text-2xl font-black text-[#00a63e]">{{ $money($box['price']) }}</p>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-[#00a63e] to-[#00c950] px-5 py-2.5 text-base font-bold text-white shadow-md">
                                        Grab It! <x-sb.icon name="crosshair" class="h-4 w-4 text-white" />
                                    </span>
                                </div>
                            </div>
                            </a>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl bg-[#f8fafc] py-16 text-center text-[#4a5565]">
                        Tidak ada box yang cocok dengan filter kamu.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Popular Restaurants — Figma 32:369 --}}
    <section class="relative overflow-hidden bg-white py-14 sm:py-20">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[rgba(255,247,237,0.5)] to-transparent"></div>
        <div class="relative mx-auto w-full px-0">
            <div class="sb-reveal text-center">
                <h2 class="text-3xl font-black sm:text-4xl md:text-5xl">
                    Popular <span class="inline-flex flex-wrap items-center gap-2 text-[#f54900]">Restaurants <x-sb.icon name="utensils" class="h-[1em] w-[1em] shrink-0 sm:h-9 sm:w-9" /></span>
                </h2>
                <p class="mt-2 text-lg text-[#4a5565]">Partner terpercaya kami</p>
            </div>
            <div class="sb-reveal-group mt-12 grid gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
                @foreach ($restaurants as $r)
                    @php
                        $linkBox = collect($catalog_boxes ?? [])->firstWhere('restaurant_id', $r['id']);
                        $restW = in_array($r['id'], $wishlistRestaurantKeys ?? [], true);
                    @endphp
                    <div class="sb-reveal group sb-hover-lift relative overflow-hidden rounded-2xl bg-white shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)] ring-1 ring-black/5">
                        <div class="absolute left-3 top-3 z-20">
                            <x-wishlist.heart type="restaurant" :target-key="$r['id']" :active="$restW" class="!h-9 !w-9" />
                        </div>
                        <a href="{{ $linkBox ? route('boxes.show', ['slug' => $linkBox['slug']]) : route('browse') }}"
                           class="block">
                        <div class="relative h-56 overflow-hidden">
                            <img src="{{ $r['image'] }}" alt="" class="sb-img-zoom h-full w-full object-cover" loading="lazy" width="800" height="600" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute right-3 top-3 flex items-center gap-1.5 rounded-full bg-gradient-to-r from-[#fdc700] to-[#ff8904] px-3 py-2 text-base font-black text-white shadow-lg">
                                <x-sb.icon name="star" class="h-5 w-5 text-white" /> {{ number_format($r['rating'], 1) }}
                            </div>
                            <div class="absolute bottom-3 left-4 flex items-center gap-2 text-base font-bold text-white">
                                <x-sb.icon name="map-pin" class="h-5 w-5 shrink-0 text-white" /> {{ $r['area'] ?? $r['city'] }}
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-xl font-black text-[#1e2939]">{{ $r['name'] }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-[#4a5565]">{{ $r['subtitle'] }}</p>
                            <div class="mt-5 border-t-2 border-[#f3f4f6] pt-4">
                                <div class="flex items-center justify-between rounded-xl border border-[#b9f8cf] bg-gradient-to-r from-[#f0fdf4] to-[#ecfdf5] px-3 py-3">
                                    <span class="flex items-center gap-2 text-sm font-black text-[#008236]">
                                        <x-sb.icon name="package" class="h-5 w-5 shrink-0 text-[#008236]" /> {{ $r['boxes_available'] ?? 1 }} Mystery Box Available
                                    </span>
                                    <x-sb.icon name="package" class="h-8 w-8 shrink-0 text-[#00a63e] opacity-40" />
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Bottom CTA — Figma orange section --}}
    <section class="sb-reveal relative mt-4 overflow-hidden rounded-2xl py-14 sm:rounded-3xl sm:py-16"
             style="background: linear-gradient(135deg, #ff8904 0%, #f54900 45%, #fdc700 100%);">
        <div class="pointer-events-none absolute inset-0 opacity-25">
            <div class="absolute -left-1/4 top-0 h-full w-1/2 bg-gradient-to-r from-white/25 to-transparent blur-3xl sb-orb-drift"></div>
            <div class="absolute -right-1/4 bottom-0 h-full w-1/2 bg-gradient-to-l from-yellow-200/30 to-transparent blur-3xl sb-orb-drift--alt"></div>
        </div>
        <div class="relative z-10 mx-auto max-w-3xl px-6 text-center text-white">
            <div class="flex justify-center gap-6 text-white/90">
                <x-sb.icon name="bolt" class="h-10 w-10 sm:h-12 sm:w-12" />
                <x-sb.icon name="rocket" class="h-10 w-10 sm:h-12 sm:w-12" />
            </div>
            <h3 class="mt-6 text-3xl font-black sm:text-4xl">Mulai Selamatkan Makanan Hari Ini!</h3>
            <p class="mt-4 text-lg text-white/95">
                Bergabunglah dengan ribuan pengguna lain dalam misi mengurangi food waste.
            </p>
            <a href="{{ route('browse') }}"
               class="sb-btn-shine sb-hover-lift sb-hover-lift--light relative mt-8 inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 text-lg font-black text-[#00a63e] shadow-xl hover:bg-[#f0fdf4] active:scale-[0.98]">
                Browse Mystery Boxes
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>
</x-layouts.app>
