<x-layouts.app :title="'Impact Tracker • SurpriseBite'" variant="marketing" active-nav="impact">
    <div class="pb-16 pt-8 sm:pt-10">
        <header class="mx-auto w-full sb-animate-up">
            <h1 class="text-3xl font-black tracking-tight text-[#1e2939] sm:text-4xl md:text-5xl">
                Impact Tracker
            </h1>
            <p class="mt-3 max-w-3xl text-base leading-relaxed text-[#6a7282] sm:text-lg">
                Lihat dampak positif yang kita ciptakan bersama dalam mengurangi food waste
            </p>
        </header>

        <div class="mx-auto mt-10 grid w-full gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
            <div class="sb-animate-up sb-delay-1 sb-stat-shine sb-hover-stat relative overflow-hidden rounded-3xl p-8 shadow-[0_10px_40px_-10px_rgba(0,166,62,0.35)]"
                 style="background: linear-gradient(140deg, rgb(0, 201, 80) 0%, rgb(0, 153, 102) 100%);">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-white">
                    <x-sb.icon name="package" class="h-8 w-8" />
                </div>
                <p class="mt-6 text-4xl font-black tabular-nums text-white sm:text-5xl">
                    <span data-sb-count="{{ $mealsSaved }}" data-sb-duration="1650">0</span>
                </p>
                <p class="mt-1 text-base font-semibold text-white/95">Total Meals Saved</p>
            </div>
            <div class="sb-animate-up sb-delay-2 sb-stat-shine sb-hover-stat relative overflow-hidden rounded-3xl p-8 shadow-[0_10px_40px_-10px_rgba(249,115,22,0.35)]"
                 style="background: linear-gradient(135deg, #ff8904 0%, #f54900 100%);">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20">
                    <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                </div>
                <p class="mt-6 text-4xl font-black tabular-nums text-white sm:text-5xl">
                    <span data-sb-count="{{ $wasteDisplay['value'] }}" data-sb-decimals="{{ $wasteDisplay['decimals'] }}" data-sb-duration="1900">0</span>
                    <span class="ml-1 text-2xl font-black sm:text-3xl">{{ $wasteDisplay['unit'] === 'kg' ? 'kg' : 'ton' }}</span>
                </p>
                <p class="mt-1 text-base font-semibold text-white/95">Food Waste Reduced</p>
            </div>
            <div class="sb-animate-up sb-delay-3 sb-stat-shine sb-hover-stat relative overflow-hidden rounded-3xl p-8 shadow-[0_10px_40px_-10px_rgba(0,166,62,0.35)] sm:col-span-2 lg:col-span-1"
                 style="background: linear-gradient(140deg, rgb(0, 201, 80) 0%, rgb(0, 153, 102) 100%);">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-white">
                    <x-sb.icon name="users" class="h-8 w-8" />
                </div>
                <p class="mt-6 text-4xl font-black tabular-nums text-white sm:text-5xl">
                    <span data-sb-count="{{ $activeUsers }}" data-sb-duration="1650">0</span>
                </p>
                <p class="mt-1 text-base font-semibold text-white/95">Active Users</p>
            </div>
        </div>

        <section class="mx-auto mt-14 w-full sb-animate-up sb-delay-2">
            <h2 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Dampak food waste</h2>
            <article class="mt-6 rounded-3xl bg-[#fff7ed] p-8 ring-1 ring-[#fed7aa] shadow-sm transition hover:shadow-md sm:p-10">
                <div class="flex flex-wrap items-center gap-3 text-[#c2410c]">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#ffedd5] text-[#c2410c]" aria-hidden="true"><x-sb.icon name="trending-down" class="h-7 w-7" /></span>
                    <h3 class="text-lg font-bold sm:text-xl">Waste reduction</h3>
                </div>
                <p class="mt-6 text-3xl font-black tabular-nums text-[#ea580c] sm:text-4xl">
                    {{ number_format($wasteDisplay['value'], $wasteDisplay['decimals'], ',', '.') }}
                    <span class="ml-2 text-2xl sm:text-3xl">{{ $wasteDisplay['unit'] === 'kg' ? 'kilogram' : 'ton' }} makanan</span>
                </p>
                <p class="mt-4 text-sm leading-relaxed text-[#4a5565] sm:text-base">
                    Setiap mystery box yang terjual = satu porsi makanan yang tidak sia-sia. Kami mengestimasi sekitar
                    <strong class="text-[#c2410c]">2,2 kg</strong> limbah makanan ritel dicegah per kotak (sisa layak konsumsi yang sebelumnya berpotensi dibuang).
                </p>
            </article>
        </section>

        @php
            $maxMeals = max(1, max(array_column($monthlyTrend, 'meals')));
            $maxWasteKg = max(0.1, max(array_column($monthlyTrend, 'waste_kg')));
        @endphp
        <section class="sb-animate-up sb-delay-3 mx-auto mt-14 w-full rounded-3xl bg-white p-6 shadow-[0_4px_24px_rgba(0,0,0,0.06)] ring-1 ring-slate-100 sm:p-8">
            <h2 class="flex items-center gap-2 text-2xl font-black text-[#1e2939] sm:text-3xl">
                <x-sb.icon name="calendar" class="h-7 w-7 shrink-0 text-[#00a63e]" aria-hidden="true" />
                Monthly Trend {{ $trendYear }}
            </h2>
            <div class="mt-8 space-y-6">
                @foreach ($monthlyTrend as $row)
                    @php
                        $gn = $row['meals'] / $maxMeals;
                        $wn = $row['waste_kg'] / $maxWasteKg;
                        $sum = $gn + $wn;
                        $greenPct = $sum > 0 ? (int) round(($gn / $sum) * 100) : 50;
                        $orangePct = 100 - $greenPct;
                        $wasteLabel = $row['waste_kg'] < 100
                            ? rtrim(rtrim(number_format($row['waste_kg'], 1, ',', ''), '0'), ',') . ' kg'
                            : rtrim(rtrim(number_format($row['waste_tons'], 2, ',', ''), '0'), ',') . ' ton';
                    @endphp
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                        <span class="w-12 shrink-0 text-base font-bold text-[#1e2939]">{{ $row['m'] }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-4 overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200/80">
                                <div class="h-full bg-[#00c950] transition-all duration-700" style="width: {{ $greenPct }}%"></div>
                                <div class="h-full bg-[#ff8904]" style="width: {{ $orangePct }}%"></div>
                            </div>
                        </div>
                        <div class="shrink-0 text-right text-sm sm:min-w-[240px]">
                            <span class="font-bold text-[#00a63e]">{{ number_format($row['meals']) }} meals</span>
                            <span class="mx-2 text-slate-300">•</span>
                            <span class="font-bold text-[#ff6900]">{{ $wasteLabel }} dicegah</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="sb-animate-up sb-delay-3 mx-auto mt-14 w-full rounded-3xl bg-white p-6 shadow-[0_4px_24px_rgba(0,0,0,0.06)] ring-1 ring-slate-100 sm:p-8">
            <h2 class="flex items-center gap-2 text-2xl font-black text-[#1e2939] sm:text-3xl">
                <x-sb.icon name="globe" class="h-7 w-7 shrink-0 text-[#00a63e]" aria-hidden="true" />
                Sustainable Development Goals (SDG)
            </h2>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-[#6a7282] sm:text-base">
                SurpriseBite selaras dengan <strong class="font-bold text-[#00a63e]">SDG 2 — Zero Hunger</strong>:
                memperluas akses makanan berkualitas dan terjangkau melalui model mystery box dari surplus restoran.
            </p>

            <article class="sb-hover-lift mt-8 overflow-hidden rounded-2xl ring-2 ring-[#00a63e]/25 sm:rounded-3xl">
                <div class="flex flex-col lg:flex-row lg:items-stretch">
                    <div class="relative flex min-h-[200px] flex-col items-center justify-center gap-3 bg-gradient-to-br from-[#00a63e] via-[#00c950] to-[#00a63e] px-8 py-10 text-white sm:min-h-[220px] lg:w-[min(340px,38%)] lg:shrink-0 lg:py-12">
                        <div class="pointer-events-none absolute inset-0 opacity-25"
                             style="background-image: radial-gradient(circle at 30% 20%, #fff 0%, transparent 45%), radial-gradient(circle at 80% 80%, #fdc700 0%, transparent 40%);"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 ring-2 ring-white/30 backdrop-blur-sm sm:h-24 sm:w-24">
                            <x-sb.icon name="utensils" class="h-11 w-11 text-white sm:h-12 sm:w-12" />
                        </div>
                        <div class="relative text-center">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-white/90">SDG 2</p>
                            <p class="mt-1 text-xl font-black sm:text-2xl">Zero Hunger</p>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col justify-center bg-gradient-to-br from-[#f0fdf4] to-white px-6 py-8 sm:px-10 sm:py-10 lg:min-h-[220px]">
                        <h3 class="text-lg font-black text-[#1e2939] sm:text-xl">Akses pangan yang adil &amp; terjangkau</h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#4a5565] sm:text-base">
                            Memastikan lebih banyak orang mendapat makanan berkualitas dengan harga yang masuk akal — dengan menyalurkan makanan sisa layak konsumsi ke meja, bukan ke tempat sampah.
                        </p>
                        <p class="mt-4 text-sm leading-relaxed text-[#6a7282] sm:text-base">
                            Setiap mystery box yang kamu ambil membantu restoran mitra mengurangi waste sekaligus membuka akses kuliner bagi konsumen yang ingin hemat tanpa kompromi rasa.
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3 border-t border-emerald-100 pt-6">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold text-[#166534] ring-1 ring-[#bbf7d0] sm:text-sm">
                                <x-sb.icon name="package" class="h-4 w-4 text-[#00a63e]" /> Surplus → piring
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold text-[#166534] ring-1 ring-[#bbf7d0] sm:text-sm">
                                <x-sb.icon name="heart" class="h-4 w-4 text-[#00a63e]" /> Dampak sosial
                            </span>
                        </div>
                    </div>
                </div>
            </article>
        </section>
    </div>
</x-layouts.app>
