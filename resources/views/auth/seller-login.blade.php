<x-layouts.app :title="'Login Seller • SurpriseBite'" variant="marketing">
    <div class="pb-16 pt-6 sm:pt-10">
        <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/10 ring-1 ring-black/5">
            <div class="lg:grid lg:min-h-[580px] lg:grid-cols-[1fr_1.05fr]">
                {{-- Brand panel --}}
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
                        <h1 class="mt-6 text-3xl font-black leading-tight sm:text-4xl">Portal Mitra / Seller</h1>
                        <p class="mt-4 max-w-sm text-base leading-relaxed text-white/90">
                            Masuk untuk mengelola toko, produk mystery box, dan pantau pesanan yang masuk dari pelanggan.
                        </p>
                    </div>
                    <p class="relative mt-10 text-sm font-semibold text-white/80 lg:mt-0">
                        Surprise<span class="text-[#fde047]">Bite</span> — save food, get surprise meals.
                    </p>
                </div>

                {{-- Form --}}
                <div class="order-1 flex flex-col justify-center px-8 py-10 sm:px-10 lg:order-2 lg:px-12 lg:py-14">
                    <div class="mx-auto w-full max-w-md">
                        <h2 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Login Mitra</h2>
                        <p class="mt-2 text-sm text-[#6a7282]">Gunakan akun mitra yang telah terdaftar.</p>

                        @if (session('status'))
                            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="post" action="{{ route('login.seller.submit') }}" class="mt-8 space-y-5">
                            @csrf

                            <div>
                                <label for="email" class="text-sm font-bold text-[#1e2939]">Email Seller</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required
                                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#0284c7] focus:outline-none focus:ring-2 focus:ring-[#0284c7]/25"
                                       placeholder="mitra@toko.com" />
                                @error('email')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="text-sm font-bold text-[#1e2939]">Password</label>
                                <input id="password" name="password" type="password" autocomplete="current-password" required
                                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#0284c7] focus:outline-none focus:ring-2 focus:ring-[#0284c7]/25"
                                       placeholder="••••••••" />
                                @error('password')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                    class="w-full rounded-full bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] py-3.5 text-base font-black text-white shadow-lg shadow-sky-900/20 transition hover:brightness-105">
                                Masuk sebagai Mitra
                            </button>
                        </form>

                        <p class="mt-6 text-center text-sm text-[#6a7282]">
                            Belum punya akun mitra?
                            <a href="{{ route('register.mitra') }}" class="font-bold text-[#0284c7] hover:underline">Daftar di sini</a>
                        </p>

                        <p class="mt-4 text-center text-sm text-[#6a7282]">
                            Bukan mitra?
                            <a href="{{ route('login') }}" class="font-bold text-[#0284c7] hover:underline">Login sebagai Pelanggan</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
