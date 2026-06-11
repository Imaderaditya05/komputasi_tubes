{{-- Satu pintu masuk: daftar mitra atau login penjual --}}
<x-layouts.app :title="'Portal Mitra • SurpriseBite'" variant="marketing">
    <div class="pb-16 pt-6 sm:pt-10">
        <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/10 ring-1 ring-black/5">
            <div class="lg:grid lg:min-h-[560px] lg:grid-cols-[1fr_1.05fr]">
                <div class="relative order-2 flex flex-col justify-between overflow-hidden bg-gradient-to-br from-[#0284c7] via-[#0ea5e9] to-[#38bdf8] px-8 py-10 text-white sm:px-10 lg:order-1 lg:px-12 lg:py-12">
                    <div class="pointer-events-none absolute -right-16 top-10 h-48 w-48 rounded-full bg-[#ff8904]/30 blur-3xl"></div>
                    <div class="pointer-events-none absolute bottom-10 left-6 h-32 w-32 rounded-full bg-white/20 blur-2xl"></div>
                    <div class="relative">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-white/90 transition hover:text-white">
                            <span aria-hidden="true">←</span> Kembali ke beranda
                        </a>
                        <div class="mt-8 mb-6" aria-hidden="true">
                            <div class="inline-flex h-14 w-auto items-center justify-center rounded-2xl bg-white px-5 shadow-lg ring-4 ring-white/20 transition hover:scale-105">
                                <img src="{{ asset('images/logo.png') }}?v={{ time() }}" class="h-8 w-auto object-contain" alt="SurpriseBite Logo" />
                            </div>
                        </div>
                        <h1 class="mt-6 text-3xl font-black leading-tight sm:text-4xl">Portal Mitra</h1>
                        <p class="mt-4 max-w-sm text-base leading-relaxed text-white/90">
                            Satu halaman untuk warung dan restoran: daftar akun baru atau masuk ke dashboard penjual.
                        </p>
                    </div>
                    <p class="relative mt-10 text-sm font-semibold text-white/80 lg:mt-0">
                        Surprise<span class="text-[#fde047]">Bite</span> — save food, get surprise meals.
                    </p>
                </div>

                <div class="order-1 flex flex-col justify-center px-8 py-10 sm:px-10 lg:order-2 lg:px-12 lg:py-14">
                    <div class="mx-auto w-full max-w-md">
                        <h2 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Pilih langkah kamu</h2>
                        <p class="mt-2 text-sm text-[#6a7282]">
                            Sudah punya akun mitra? Masuk. Belum? Buat akun untuk mulai mengelola mystery box.
                        </p>
                        <p class="mt-3 rounded-xl border border-amber-200/80 bg-amber-50 px-3 py-2 text-xs font-semibold leading-relaxed text-amber-950">
                            Mitra baru akan menunggu validasi admin biasanya 1–3 hari kerja sebelum dashboard aktif.
                        </p>

                        <div class="mt-8 space-y-3">
                            <a href="{{ route('login.seller') }}"
                               class="flex w-full items-center justify-center rounded-full bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] py-3.5 text-base font-black text-white shadow-lg shadow-sky-900/20 transition hover:brightness-105">
                                Masuk ke dashboard mitra
                            </a>
                            <a href="{{ route('register.mitra') }}"
                               class="flex w-full items-center justify-center rounded-full border-2 border-slate-200 bg-[#fafafa] py-3.5 text-base font-black text-[#1e2939] transition hover:border-slate-300 hover:bg-slate-50">
                                Daftar akun mitra (warung / resto)
                            </a>
                        </div>

                        <p class="mt-8 text-center text-sm text-[#6a7282]">
                            Ingin belanja sebagai pelanggan?
                            <a href="{{ route('login') }}" class="font-bold text-[#0284c7] hover:underline">Login pelanggan</a>
                        </p>
                        <p class="mt-3 text-center text-sm text-[#6a7282]">
                            Admin?
                            <a href="{{ route('login.admin') }}" class="font-bold text-[#0284c7] hover:underline">Login Admin</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
