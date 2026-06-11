{{-- Figma node 38:1205 — customer login --}}
<x-layouts.app :title="'Login • SurpriseBite'" variant="marketing">
    <div class="pb-16 pt-6 sm:pt-10">
        <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/10 ring-1 ring-black/5">
            <div class="lg:grid lg:min-h-[580px] lg:grid-cols-[1fr_1.05fr]">
                {{-- Brand panel --}}
                <div class="relative order-2 flex flex-col justify-between overflow-hidden bg-gradient-to-br from-[#00a63e] via-[#00c950] to-[#00bc7d] px-8 py-10 text-white sm:px-10 lg:order-1 lg:px-12 lg:py-12">
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
                        <h1 class="mt-6 text-3xl font-black leading-tight sm:text-4xl">Selamat datang kembali!</h1>
                        <p class="mt-4 max-w-sm text-base leading-relaxed text-white/90">
                            Masuk untuk lanjut checkout mystery box dan lihat dampak food waste yang kamu kurangi.
                        </p>
                    </div>
                    <p class="relative mt-10 text-sm font-semibold text-white/80 lg:mt-0">
                        Surprise<span class="text-[#fde047]">Bite</span> — save food, get surprise meals.
                    </p>
                </div>

                {{-- Form --}}
                <div class="order-1 flex flex-col justify-center px-8 py-10 sm:px-10 lg:order-2 lg:px-12 lg:py-14">
                    <div class="mx-auto w-full max-w-md">
                        <h2 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Login Pelanggan</h2>
                        <p class="mt-2 text-sm text-[#6a7282]">Gunakan akun pelanggan yang sudah terdaftar. Untuk admin, gunakan Login Admin. Untuk mitra/warung, buka Portal Mitra di bawah.</p>

                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold leading-relaxed text-amber-950">
                            <span class="font-black">Kamu admin?</span> Jangan pakai form ini. Masuk lewat
                            <a href="{{ route('login.admin') }}" class="font-black text-[#00a63e] underline decoration-2 underline-offset-2 hover:text-[#14532d]">halaman Login Admin</a>
                            — di sana kamu akan diarahkan ke dashboard admin.
                        </div>

                        <form method="post" action="{{ route('login.submit') }}" class="mt-8 space-y-5">
                            @csrf

                            <div>
                                <label for="email" class="text-sm font-bold text-[#1e2939]">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required
                                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                                       placeholder="nama@email.com" />
                                @error('email')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="text-sm font-bold text-[#1e2939]">Password</label>
                                <input id="password" name="password" type="password" autocomplete="current-password" required
                                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                                       placeholder="••••••••" />
                                @error('password')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                    class="w-full rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] py-3.5 text-base font-black text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-105">
                                Masuk
                            </button>
                        </form>

                        <p class="mt-8 text-center text-sm text-[#6a7282]">
                            Belum punya akun?
                            <a href="{{ route('register') }}" class="font-bold text-[#00a63e] hover:underline">Daftar sekarang</a>
                        </p>

                        <p class="mt-6 flex flex-col items-center justify-center space-y-2 text-xs text-[#9ca3af]">
                            <a href="{{ route('mitra.portal') }}" class="font-bold text-[#00a63e] hover:underline">Portal Mitra — daftar atau login penjual</a>
                            <a href="{{ route('login.admin') }}" class="font-bold text-[#00a63e] hover:underline">Login Admin</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
