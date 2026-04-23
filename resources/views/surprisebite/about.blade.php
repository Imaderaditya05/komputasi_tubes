<x-layouts.app :title="'Tentang • SurpriseBite'" variant="marketing" active-nav="about">
    <div class="w-full pb-20 pt-8 sm:pt-10">
        {{-- Hero — lebar penuh seperti halaman marketing lain --}}
        <section class="text-center">
            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-3xl bg-white p-3 shadow-xl shadow-slate-200/50 ring-1 ring-slate-100 sm:h-28 sm:w-28 transition hover:scale-105 duration-300">
                <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="SurpriseBite Logo" class="h-full w-full object-contain drop-shadow" />
            </div>
            <h1 class="mt-8 text-3xl font-black tracking-tight text-[#1e2939] sm:text-4xl md:text-5xl">
                Tentang <span class="text-[#00a63e]">SurpriseBite</span>
            </h1>
            <p class="mx-auto mt-4 max-w-4xl text-base leading-relaxed text-[#6a7282] sm:text-lg md:text-xl">
                SurpriseBite adalah platform inovatif yang menghubungkan konsumen dengan restoran lokal untuk mengurangi food waste
                melalui mystery box berkualitas dengan harga terjangkau — kejutan lezat yang juga baik untuk planet.
            </p>
        </section>

        {{-- Mission & Vision — grid full width container --}}
        <div class="mt-14 grid w-full gap-6 lg:grid-cols-2 lg:gap-8">
            <article class="sb-hover-stat rounded-3xl p-8 text-white shadow-xl sm:p-10 lg:min-h-[280px]"
                     style="background: linear-gradient(140deg, rgb(0, 201, 80) 0%, rgb(0, 153, 102) 100%);">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-white ring-1 ring-white/25" aria-hidden="true"><x-sb.icon name="rocket" class="h-8 w-8" /></div>
                <h2 class="mt-6 text-2xl font-black sm:text-3xl">Our Mission</h2>
                <p class="mt-4 text-base leading-relaxed text-white/95 sm:text-lg">
                    Menciptakan sistem win-win bagi konsumen, restoran, dan lingkungan: makanan sisa layak konsumsi tidak terbuang,
                    bisnis tetap berputar, dan kamu menikmati hidangan surprise dengan harga adil.
                </p>
            </article>
            <article class="sb-hover-stat rounded-3xl p-8 text-white shadow-xl sm:p-10 lg:min-h-[280px]"
                     style="background: linear-gradient(135deg, #ff8904 0%, #f54900 100%);">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-white ring-1 ring-white/25" aria-hidden="true"><x-sb.icon name="heart" class="h-8 w-8" /></div>
                <h2 class="mt-6 text-2xl font-black sm:text-3xl">Our Vision</h2>
                <p class="mt-4 text-base leading-relaxed text-white/95 sm:text-lg">
                    Menjadi platform terdepan di Indonesia untuk ekosistem kuliner berkelanjutan — di mana setiap meal diselamatkan
                    berarti langkah kecil menuju dunia tanpa food waste.
                </p>
            </article>
        </div>

        {{-- Cara Kerja --}}
        <section class="mt-16 w-full rounded-3xl bg-[#fafafa] p-6 ring-1 ring-slate-200/80 sm:p-10 md:p-12">
            <h2 class="text-center text-2xl font-black text-[#1e2939] sm:text-3xl">Cara Kerja</h2>
            <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['n' => 1, 'tone' => 'green', 'title' => 'Browse Mystery Boxes', 'body' => 'Jelajahi box dari restoran di sekitarmu dan pilih yang paling cocok.'],
                    ['n' => 2, 'tone' => 'orange', 'title' => 'Pesan Box', 'body' => 'Checkout cepat dengan pickup atau delivery sesuai kebutuhanmu.'],
                    ['n' => 3, 'tone' => 'green', 'title' => 'Pickup', 'body' => 'Ambil atau tunggu di antar pada jendela waktu yang tertera.'],
                    ['n' => 4, 'tone' => 'orange', 'title' => 'Enjoy!', 'body' => 'Buka kejutan makananmu dan nikmati dengan harga hemat.'],
                ] as $step)
                    <div class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full text-lg font-black {{ $step['tone'] === 'green' ? 'bg-[#dcfce7] text-[#166534]' : 'bg-[#ffedd5] text-[#c2410c]' }}">
                            {{ $step['n'] }}
                        </div>
                        <h3 class="mt-4 text-base font-black text-[#1e2939]">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[#6a7282]">{{ $step['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Keunggulan — grid terpusat + kartu penuh per sel (hindari “lubang” di tengah) --}}
        <section class="mt-16 w-full rounded-3xl bg-white p-6 ring-1 ring-slate-200/80 sm:p-10 md:p-12">
            <h2 class="text-center text-2xl font-black text-[#1e2939] sm:text-3xl">Keunggulan Kami</h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-sm text-[#6a7282] sm:text-base">
                Empat alasan utama kenapa mystery box SurpriseBite cocok untuk kamu, restoran, dan lingkungan.
            </p>
            <div class="mx-auto mt-10 grid w-full max-w-lg grid-cols-1 gap-5 sm:max-w-5xl sm:grid-cols-2 sm:gap-6">
                @foreach ([
                    ['green', 'leaf', 'Mengurangi Food Waste', 'Setiap pembelian membantu makanan layak konsumsi tidak berakhir di tempat sampah.'],
                    ['orange', 'tag', 'Harga Terjangkau', 'Nikmati kualitas restoran dengan diskon besar lewat konsep mystery box.'],
                    ['green', 'bakery', 'Dukung Bisnis Lokal', 'Restoran mitra mendapat pemasukan tambahan dari stok yang tersisa.'],
                    ['orange', 'globe', 'Kontribusi Lingkungan', 'Kurangi limbah makanan bersama komunitas SurpriseBite — langkah kecil yang terasa di rantai pangan.'],
                ] as [$tone, $icon, $t, $d])
                    <article class="sb-hover-lift flex h-full flex-col gap-4 rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50/90 p-6 shadow-sm ring-1 ring-black/[0.04] sm:p-7 {{ $tone === 'green' ? 'hover:border-emerald-200/90 hover:ring-emerald-100/80' : 'hover:border-orange-200/90 hover:ring-orange-100/80' }}">
                        <div class="flex items-start gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-white shadow-md {{ $tone === 'green' ? 'bg-gradient-to-br from-[#00a63e] to-[#00c950] shadow-emerald-900/20' : 'bg-gradient-to-br from-[#ff8904] to-[#f54900] shadow-orange-900/20' }}">
                                <x-sb.icon :name="$icon" class="h-6 w-6 text-white" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-black leading-snug text-[#1e2939] sm:text-lg">{{ $t }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-[#6a7282] sm:text-[15px]">{{ $d }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Our Impact banner — selaras lebar penuh; tanpa metrik CO₂ --}}
        <section class="mt-16 w-full overflow-hidden rounded-3xl p-8 text-center text-white shadow-xl sm:p-12 md:p-14"
                 style="background: linear-gradient(120deg, rgb(0, 166, 62) 0%, rgb(0, 201, 80) 45%, rgb(255, 137, 4) 100%);">
            <div class="flex justify-center text-white/90" aria-hidden="true"><x-sb.icon name="leaf" class="h-14 w-14 sm:h-16 sm:w-16" /></div>
            <h2 class="mt-4 text-3xl font-black sm:text-4xl">Our Impact</h2>
            <div class="mt-10 grid w-full gap-8 sm:grid-cols-2 sm:gap-10 lg:gap-14">
                <div class="rounded-2xl bg-white/10 px-4 py-6 ring-1 ring-white/20 backdrop-blur-[2px] sm:px-6">
                    <div class="flex justify-center text-white/90" aria-hidden="true"><x-sb.icon name="package" class="h-10 w-10 sm:h-12 sm:w-12" /></div>
                    <p class="mt-3 text-3xl font-black tabular-nums sm:text-4xl md:text-5xl"><span data-sb-count="{{ $impactMeals }}" data-sb-duration="1650">0</span>+</p>
                    <p class="mt-1 text-sm font-semibold text-white/90 sm:text-base">Makanan diselamatkan</p>
                </div>
                <div class="rounded-2xl bg-white/10 px-4 py-6 ring-1 ring-white/20 backdrop-blur-[2px] sm:px-6">
                    <div class="flex justify-center text-white/90" aria-hidden="true"><x-sb.icon name="users" class="h-10 w-10 sm:h-12 sm:w-12" /></div>
                    <p class="mt-3 text-3xl font-black tabular-nums sm:text-4xl md:text-5xl"><span data-sb-count="{{ $impactActiveUsers }}" data-sb-duration="1650">0</span>+</p>
                    <p class="mt-1 text-sm font-semibold text-white/90 sm:text-base">Pengguna aktif</p>
                </div>
            </div>
        </section>

        {{-- SDG — lebar penuh seperti banner & Impact page --}}
        <section class="mt-16 w-full rounded-3xl bg-white p-6 shadow-[0_4px_24px_rgba(0,0,0,0.06)] ring-1 ring-slate-100 sm:p-8 md:p-10">
            <h2 class="flex flex-wrap items-center gap-2 text-2xl font-black text-[#1e2939] sm:text-3xl">
                <x-sb.icon name="globe" class="h-7 w-7 shrink-0 text-[#00a63e]" aria-hidden="true" />
                Mendukung Sustainable Development Goals
            </h2>
            <p class="mt-3 max-w-4xl text-sm leading-relaxed text-[#6a7282] sm:text-base md:text-lg">
                Kami mengacu pada <strong class="font-bold text-[#00a63e]">SDG 2 — Zero Hunger</strong> dari tujuan pembangunan berkelanjutan PBB.
            </p>

            <article class="sb-hover-lift mt-8 overflow-hidden rounded-2xl ring-2 ring-[#86efac] sm:rounded-3xl">
                <div class="flex flex-col lg:flex-row lg:items-stretch">
                    <div class="relative flex min-h-[200px] flex-col items-center justify-center gap-3 bg-gradient-to-br from-[#166534] via-[#15803d] to-[#14532d] px-8 py-10 text-white sm:min-h-[220px] lg:w-[min(380px,34%)] lg:shrink-0 lg:py-12 xl:w-[min(420px,32%)]">
                        <div class="pointer-events-none absolute inset-0 opacity-20"
                             style="background-image: radial-gradient(circle at 25% 25%, #fff 0%, transparent 42%), radial-gradient(circle at 85% 70%, #fde047 0%, transparent 35%);"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 ring-2 ring-white/25 backdrop-blur-sm sm:h-24 sm:w-24">
                            <x-sb.icon name="utensils" class="h-11 w-11 text-white sm:h-12 sm:w-12" />
                        </div>
                        <div class="relative text-center">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-white/90">SDG 2</p>
                            <p class="mt-1 text-xl font-black sm:text-2xl">Zero Hunger</p>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col justify-center bg-gradient-to-br from-[#f0fdf4] to-white px-6 py-8 sm:px-10 sm:py-10">
                        <h3 class="text-lg font-black text-[#14532d] sm:text-xl">Akses makanan bergizi &amp; terjangkau</h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#365314]/95 sm:text-base">
                            Akses makanan bergizi dengan harga terjangkau melalui penyelamatan surplus restoran — makanan layak konsumsi sampai ke meja, bukan ke tempat sampah.
                        </p>
                        <p class="mt-4 text-sm leading-relaxed text-[#3f6212]/90 sm:text-base">
                            Dengan satu platform, konsumen hemat, restoran mengurangi sisa stok, dan pangan yang masih layak dimanfaatkan sepenuhnya.
                        </p>
                    </div>
                </div>
            </article>
        </section>
    </div>
</x-layouts.app>
