@php
    $mapsQuery = urlencode($restaurant['name'] . ' ' . $restaurant['area'] . ' ' . $restaurant['city']);
    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . $mapsQuery;
    $restWishlisted = in_array($restaurant['id'], $wishlistRestaurantKeys ?? [], true);
    $boxWishlisted = in_array($box['slug'], $wishlistMenuSlugs ?? [], true);
@endphp

<x-layouts.app :title="$box['title'].' • SurpriseBite'" variant="marketing">
    <div class="pb-16 pt-4 sm:pt-6">
        <a href="{{ route('browse') }}" class="inline-flex items-center gap-1 text-sm font-bold text-[#364153] hover:text-[#00a63e]">
            ← Kembali
        </a>

        <div class="mt-4 overflow-hidden rounded-3xl bg-white shadow-lg shadow-black/10 ring-1 ring-slate-100">
            <div class="relative aspect-[21/9] min-h-[200px] sm:min-h-[280px]">
                <div class="absolute right-4 top-4 z-20">
                    <x-wishlist.heart type="restaurant" :target-key="$restaurant['id']" :active="$restWishlisted" class="!h-10 !w-10" />
                </div>
                <img src="{{ $restaurant['image'] }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-5 sm:p-8">
                    <h1 class="text-2xl font-black text-white sm:text-4xl">{{ $restaurant['name'] }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-white/95">
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 font-bold backdrop-blur-sm ring-1 ring-white/30">
                            <x-sb.icon name="star" class="h-4 w-4 text-amber-300" /> {{ number_format($restaurant['rating'], 1) }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-semibold"><x-sb.icon name="map-pin" class="h-4 w-4 shrink-0 text-white" /> {{ $restaurant['area'] }}</span>
                    </div>
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"
                       class="mt-4 inline-flex rounded-full bg-white px-5 py-2.5 text-sm font-black text-[#00a63e] shadow-md hover:bg-[#f0fdf4]">
                        Lihat di Maps
                    </a>
                </div>
            </div>
        </div>

        <section class="mt-10">
            <h2 class="text-xl font-black text-[#1e2939] sm:text-2xl">Available Mystery Boxes</h2>
            <div class="mt-5 max-w-xl overflow-hidden rounded-3xl bg-white shadow-md ring-1 ring-slate-100">
                <div class="relative aspect-[16/10] sm:aspect-[2/1]">
                    <div class="absolute left-3 top-3 z-20">
                        <x-wishlist.heart type="menu" :target-key="$box['slug']" :active="$boxWishlisted" class="!h-9 !w-9" />
                    </div>
                    <img src="{{ $box['image'] }}" alt="" class="h-full w-full object-cover">
                    <span class="absolute right-3 top-3 rounded-full bg-gradient-to-r from-[#ff8904] to-[#f54900] px-2.5 py-1 text-xs font-black text-white shadow">
                        @php $dp = $box['original_price'] > 0 ? (int) round(100 - ($box['price'] / $box['original_price']) * 100) : 0; @endphp
                        -{{ $dp }}%
                    </span>
                    @if ($box['stock'] <= 3)
                        <span class="absolute left-3 top-3 rounded-full bg-red-600 px-2.5 py-1 text-xs font-black text-white shadow">
                            Only {{ $box['stock'] }} left!
                        </span>
                    @endif
                    <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-xs font-bold text-[#1e2939] shadow">
                        <x-sb.icon name="star" class="h-3.5 w-3.5 text-amber-500" /> {{ number_format($box['card_rating'], 1) }}
                    </span>
                </div>
                <div class="p-5 sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#00a63e]">{{ $box['category_label'] }}</p>
                    <h3 class="mt-1 text-xl font-black text-[#1e2939] sm:text-2xl">{{ $box['title'] }}</h3>
                    <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-[#fff7ed] px-3 py-1.5 text-xs font-bold text-[#c2410c] ring-1 ring-[#fed7aa]">
                        <x-sb.icon name="clock" class="h-3.5 w-3.5 shrink-0" /> {{ $box['pickup_time'] }}
                    </p>
                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm text-[#9ca3af] line-through">{{ $money($box['original_price']) }}</p>
                            <p class="text-2xl font-black text-[#00a63e] sm:text-3xl">{{ $money($box['price']) }}</p>
                        </div>
                        <div class="flex gap-2">
                            <form action="{{ route('cart.add') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="box_slug" value="{{ $box['slug'] }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-full bg-blue-500 hover:bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add to Cart
                                </button>
                            </form>
                            <a href="{{ route('checkout.delivery', ['slug' => $box['slug']]) }}"
                               class="inline-flex items-center justify-center gap-1.5 rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-8 py-3 text-sm font-black text-white shadow-lg shadow-emerald-900/20">
                                Grab It! <x-sb.icon name="package" class="h-4 w-4 text-white" />
                            </a>
                        </div>
                    </div>
                    @php
                        $authBox = session('auth', []);
                        $isCustomer = is_array($authBox) && ($authBox['role'] ?? null) === 'user';
                    @endphp
                    @if (! $isCustomer)
                        <p class="mt-3 text-xs leading-relaxed text-[#6a7282]">
                            Belum login? Klik <strong class="text-[#1e2939]">Grab It</strong> — kamu akan diarahkan ke login sebagai <strong>user</strong>, lalu lanjut checkout otomatis.
                        </p>
                    @endif
                </div>
            </div>
        </section>

        <p class="mt-8 max-w-2xl text-sm leading-relaxed text-[#6a7282]">
            {{ $box['description'] }}
        </p>
    </div>
</x-layouts.app>
