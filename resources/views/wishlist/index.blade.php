<x-layouts.app :title="'Wishlist • SurpriseBite'" variant="marketing">
    <div class="mx-auto max-w-5xl pb-16 pt-6 sm:pt-10">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Wishlist</h1>
                <p class="mt-2 text-sm text-[#6a7282]">Restoran dan menu yang kamu simpan.</p>
            </div>
            <a href="{{ route('browse') }}" class="text-sm font-bold text-[#00a63e] hover:underline">← Kembali ke Browse</a>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        <section class="mt-10">
            <h2 class="text-lg font-black text-[#1e2939]">Restoran</h2>
            @if (count($restaurants) === 0)
                <p class="mt-3 text-sm text-[#6a7282]">Belum ada restoran favorit.</p>
            @else
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($restaurants as $r)
                        @php
                            $firstSlug = $boxSlugByRestaurantId[$r['id']] ?? null;
                        @endphp
                        <div class="relative overflow-hidden rounded-2xl bg-white p-4 shadow-md ring-1 ring-slate-100">
                            <div class="absolute right-0 top-0">
                                <x-wishlist.heart
                                    type="restaurant"
                                    :target-key="$r['id']"
                                    :active="true"
                                    class="!h-9 !w-9"
                                />
                            </div>
                            <div class="pr-10">
                                <p class="font-black text-[#1e2939]">{{ $r['name'] }}</p>
                                <p class="mt-1 text-xs text-[#6a7282]">{{ $r['area'] ?? $r['city'] ?? '' }}</p>
                            </div>
                            @if ($firstSlug)
                                <a href="{{ route('boxes.show', ['slug' => $firstSlug]) }}" class="mt-3 inline-flex text-sm font-bold text-[#00a63e] hover:underline">Lihat mystery box</a>
                            @else
                                <a href="{{ route('browse', ['q' => $r['name']]) }}" class="mt-3 inline-flex text-sm font-bold text-[#00a63e] hover:underline">Cari di Browse</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-12">
            <h2 class="text-lg font-black text-[#1e2939]">Menu / mystery box</h2>
            @if (count($menus) === 0)
                <p class="mt-3 text-sm text-[#6a7282]">Belum ada menu yang disimpan.</p>
            @else
                <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($menus as $row)
                        @php
                            $box = $row['box'];
                            $rest = $row['restaurant'];
                        @endphp
                        <article class="overflow-hidden rounded-3xl bg-white shadow-md shadow-black/5 ring-1 ring-slate-100">
                            <div class="relative aspect-[4/3] overflow-hidden">
                                <img src="{{ $box['image'] }}" alt="" class="h-full w-full object-cover">
                                <div class="absolute right-2 top-2">
                                    <x-wishlist.heart
                                        type="menu"
                                        :target-key="$box['slug']"
                                        :active="true"
                                        class="!h-9 !w-9"
                                    />
                                </div>
                            </div>
                            <div class="p-4">
                                <p class="text-xs font-semibold text-[#6a7282]">{{ $box['category_label'] ?? '' }}</p>
                                <h3 class="mt-1 font-black text-[#1e2939]">{{ $box['title'] }}</h3>
                                <p class="mt-0.5 text-sm text-[#6a7282]">{{ data_get($rest, 'name') }}</p>
                                <div class="mt-3 flex items-end justify-between gap-2">
                                    <p class="text-lg font-black text-[#00a63e]">{{ $money((int) ($box['price'] ?? 0)) }}</p>
                                    <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}" class="rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-4 py-2 text-xs font-black text-white shadow">Detail</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
