@php
    $mapsQuery = urlencode($restaurant['name'] . ' ' . $restaurant['area'] . ' ' . $restaurant['city']);
    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . $mapsQuery;
    $restWishlisted = in_array($restaurant['id'], $wishlistRestaurantKeys ?? [], true);
    $boxWishlisted = in_array($box['slug'], $wishlistMenuSlugs ?? [], true);
    $stk = (int) ($box['stock'] ?? 0);
    $oosNotice = 'Menu tidak dapat ditambahkan karena stok tidak tersedia.';
@endphp

@if ($stk <= 0)
    @push('body-scripts')
        <script>
            (function () {
                if (typeof globalThis !== 'undefined' && globalThis.__sbStockOosNotifyBound === true) {
                    return;
                }
                if (typeof globalThis !== 'undefined') {
                    globalThis.__sbStockOosNotifyBound = true;
                }

                var DEFAULT = @json($oosNotice);

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
@endif

<x-layouts.app :title="$box['title'].' • SurpriseBite'" variant="marketing">
    <div class="pb-16 pt-4 sm:pt-6" data-sb-box-page data-sb-box-slug="{{ $box['slug'] }}">
        <a href="{{ route('browse') }}" class="inline-flex items-center gap-1 text-sm font-bold text-[#364153] hover:text-[#00a63e]">
            ← Kembali
        </a>

        @if (session('status'))
            <div class="mb-4 mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-950 ring-1 ring-emerald-100" role="status">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-950 ring-1 ring-red-100" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
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
                        <span class="inline-flex items-center gap-1 font-semibold" role="presentation"><x-sb.icon name="map-pin" class="h-4 w-4 shrink-0 text-white" aria-hidden="true" /> {{ $restaurant['area'] }}</span>
                    </div>
                    <a href="{{ $mapsUrl }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="mt-4 inline-flex rounded-full bg-white px-5 py-2.5 text-sm font-black text-[#00a63e] shadow-md outline-offset-4 hover:bg-[#f0fdf4] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/85 focus-visible:ring-offset-2 focus-visible:ring-offset-black/30"
                       data-sb-external-google-maps
                       aria-label="Buka lokasi restoran {{ $restaurant['name'] }} di Google Maps (tab baru)">
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
                    <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-xs font-bold text-[#1e2939] shadow">
                        <x-sb.icon name="star" class="h-3.5 w-3.5 text-amber-500" /> {{ number_format((float) ($box['card_rating'] ?? 0), 1) }}@if ((int) ($box['ratings_count'] ?? 0) > 0)<span class="text-[10px] font-semibold text-[#6a7282]">({{ $box['ratings_count'] }})</span>@endif
                    </span>
                </div>
                <div class="p-5 sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#00a63e]">{{ $box['category_label'] }}</p>
                    <h3 class="mt-1 text-xl font-black text-[#1e2939] sm:text-2xl">{{ $box['title'] }}</h3>
                    <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-[#fff7ed] px-3 py-1.5 text-xs font-bold text-[#c2410c] ring-1 ring-[#fed7aa]">
                        <x-sb.icon name="clock" class="h-3.5 w-3.5 shrink-0" aria-hidden="true" /> {{ $box['pickup_time'] }}
                    </p>
                    <p class="mt-3 text-sm font-bold {{ $stk > 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                        @if ($stk > 0) Stok tersedia: {{ $stk }} @else Stok habis — tidak bisa dipesan. @endif
                    </p>
                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                        <div class="relative z-10">
                            <p class="text-sm text-[#9ca3af] line-through">{{ $money($box['original_price']) }}</p>
                            <p class="text-2xl font-black text-[#00a63e] sm:text-3xl">{{ $money($box['price']) }}</p>
                        </div>
                        <div class="relative z-20 flex flex-wrap gap-2">
                            @if ($stk > 0)
                                <form action="{{ route('cart.add') }}" method="POST" class="relative z-20 inline">
                                    @csrf
                                    <input type="hidden" name="box_slug" value="{{ $box['slug'] }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button
                                        type="submit"
                                        class="relative z-20 inline-flex items-center justify-center gap-1.5 rounded-full bg-blue-500 px-6 py-3 text-sm font-black text-white shadow-lg transition hover:bg-blue-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/55 focus-visible:ring-offset-2"
                                        data-sb-add-to-cart
                                        aria-label="{{ $box['title'] }} — tambah ke keranjang"
                                    >
                                        <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add to Cart
                                    </button>
                                </form>
                                <a href="{{ route('checkout.delivery', ['slug' => $box['slug']]) }}"
                                   class="relative z-30 inline-flex items-center justify-center gap-1.5 rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-8 py-3 text-sm font-black text-white shadow-lg shadow-emerald-900/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#008236]/55 focus-visible:ring-offset-2"
                                   data-sb-grab-it
                                   data-sb-target="checkout-delivery"
                                   aria-label="{{ $box['title'] }} — Grab It, lanjut pemilihan delivery atau pickup">
                                    Grab It! <x-sb.icon name="package" class="h-4 w-4 shrink-0 text-white" aria-hidden="true" />
                                </a>
                            @else
                                <button
                                    type="button"
                                    data-sb-oos-notify
                                    data-sb-oos-message="{{ e($oosNotice) }}"
                                    class="relative z-20 inline-flex min-h-[48px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-blue-600 bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                    style="min-height:48px;box-sizing:border-box;background-color:#2563eb;color:#fff;border:2px solid #1d4ed8;border-radius:9999px;padding:0.75rem 1.5rem;font-size:0.875rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;cursor:pointer;"
                                    aria-label="{{ $oosNotice }}"
                                >
                                    <svg class="h-4 w-4 shrink-0" style="color:#fff" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span style="color:#fff">Add to Cart</span>
                                </button>
                                <button
                                    type="button"
                                    data-sb-oos-notify
                                    data-sb-oos-message="{{ e($oosNotice) }}"
                                    class="relative z-30 inline-flex min-h-[48px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-slate-600 bg-gradient-to-r from-slate-500 to-slate-600 px-8 py-3 text-sm font-black text-white shadow-lg transition hover:from-slate-600 hover:to-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2"
                                    style="min-height:48px;box-sizing:border-box;background:linear-gradient(to right,#64748b,#475569);color:#fff;border:2px solid #334155;border-radius:9999px;padding:0.75rem 2rem;font-size:0.875rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;cursor:pointer;"
                                    data-sb-grab-it-disabled
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
                    @php
                        $authBox = session('auth', []);
                        $isCustomer = is_array($authBox) && ($authBox['role'] ?? null) === 'user';
                    @endphp
                    @if (! $isCustomer && $stk > 0)
                        <p class="mt-3 text-xs leading-relaxed text-[#6a7282]">
                            Belum login? Klik <strong class="text-[#1e2939]">Grab It</strong> — kamu akan diarahkan ke login sebagai <strong>user</strong>, lalu lanjut checkout otomatis.
                        </p>
                    @endif
                    @auth
                        @if(auth()->user()->role === 'customer' && ! empty($pendingRatingOrderId))
                            <x-browse.pending-rating :public-order-id="$pendingRatingOrderId" class="mt-4" />
                        @endif
                    @endauth
                </div>
            </div>
        </section>

        <p class="mt-8 max-w-2xl text-sm leading-relaxed text-[#6a7282]">
            {{ $box['description'] }}
        </p>
        <p class="mt-6 max-w-2xl text-sm font-semibold text-[#6a7282]">
            Setelah pesanan Anda <strong class="text-[#1e2939]">selesai</strong>, Anda dapat memberi penilaian dan melaporkan mitra dari halaman <strong class="text-[#00a63e]">Lacak pesanan</strong> di menu pesanan.
        </p>
    </div>
</x-layouts.app>