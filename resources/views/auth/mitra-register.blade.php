<x-layouts.app :title="'Daftar Mitra • SurpriseBite'" variant="marketing">
    <div class="pb-16 pt-6 sm:pt-10">
        <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/10 ring-1 ring-black/5">
            <div class="lg:grid lg:min-h-[640px] lg:grid-cols-[1fr_1.05fr]">
                <div class="relative order-2 flex flex-col justify-between overflow-hidden bg-gradient-to-br from-[#00a63e] via-[#00bc7d] to-[#059669] px-8 py-10 text-white sm:px-10 lg:order-1 lg:px-12 lg:py-12">
                    <div class="pointer-events-none absolute -right-16 top-10 h-48 w-48 rounded-full bg-[#ff8904]/25 blur-3xl"></div>
                    <div class="pointer-events-none absolute bottom-10 left-6 h-32 w-32 rounded-full bg-white/15 blur-2xl"></div>
                    <div class="relative">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-white/90 transition hover:text-white">
                            <span aria-hidden="true">←</span> Kembali ke beranda
                        </a>
                        <div class="mt-8 mb-6" aria-hidden="true">
                            <div class="inline-flex h-14 w-auto items-center justify-center rounded-2xl bg-white px-5 shadow-lg ring-4 ring-white/20 transition hover:scale-105">
                                <img src="{{ asset('images/logo.png') }}?v={{ time() }}" class="h-8 w-auto object-contain" alt="SurpriseBite Logo" />
                            </div>
                        </div>
                        <h1 class="mt-6 text-3xl font-black leading-tight sm:text-4xl">Daftar sebagai Mitra</h1>
                        <p class="mt-4 max-w-sm text-base leading-relaxed text-white/95">
                            Kelola mystery box, stok, dan pesanan dari satu dashboard. Gratis mendaftar — cocok untuk warung dan restoran.
                        </p>
                        <ul class="mt-8 space-y-3 text-sm font-semibold text-white/95">
                            <li class="flex items-center gap-2"><x-sb.icon name="check" class="h-4 w-4 shrink-0 text-white" /> Dashboard mitra lengkap</li>
                            <li class="flex items-center gap-2"><x-sb.icon name="check" class="h-4 w-4 shrink-0 text-white" /> Atur menu &amp; stok real-time</li>
                            <li class="flex items-center gap-2"><x-sb.icon name="check" class="h-4 w-4 shrink-0 text-white" /> Satu akun per tim toko</li>
                        </ul>
                    </div>
                    <p class="relative mt-10 text-sm text-white/85 lg:mt-0">
                        Sudah punya akun?
                        <a href="{{ route('login.seller') }}" class="font-black underline decoration-2 underline-offset-2 hover:text-white">Login Mitra</a>
                    </p>
                </div>

                <div class="order-1 flex flex-col justify-center px-8 py-10 sm:px-10 lg:order-2 lg:px-12 lg:py-14">
                    <div class="mx-auto w-full max-w-md">
                        <h2 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Buat akun mitra</h2>
                        <p class="mt-2 text-sm text-[#6a7282]">Isi data di bawah. Setelah terdaftar, gunakan email ini di halaman login mitra.</p>

                        <form method="post" action="{{ route('register.mitra.submit') }}" class="mt-8 space-y-5">
                            @csrf

                            <div>
                                <label for="name" class="text-sm font-bold text-[#1e2939]">Nama / nama warung</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="organization" required
                                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                                       placeholder="Contoh: Warung Sunda Cita Rasa" />
                                @error('name')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="text-sm font-bold text-[#1e2939]">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required
                                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                                       placeholder="mitra@toko.com" />
                                @error('email')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="text-sm font-bold text-[#1e2939]">Password</label>
                                <input id="password" name="password" type="password" autocomplete="new-password" required
                                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                                       placeholder="Minimal 8 karakter" />
                                @error('password')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="text-sm font-bold text-[#1e2939]">Konfirmasi password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                                       class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                                       placeholder="Ulangi password" />
                            </div>

                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-[#fafafa] p-4">
                                <input type="checkbox" name="terms" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#00a63e] focus:ring-[#00a63e]" @checked(old('terms')) />
                                <span class="text-xs leading-relaxed text-[#4a5565]">
                                    Saya menyetujui <span class="font-bold text-[#1e2939]">Syarat &amp; Ketentuan</span> serta <span class="font-bold text-[#1e2939]">Kebijakan Privasi</span> SurpriseBite.
                                </span>
                            </label>
                            @error('terms')
                                <p class="text-xs font-bold text-red-600">{{ $message }}</p>
                            @enderror

                            <button type="submit"
                                    class="w-full rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] py-3.5 text-base font-black text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-105">
                                Daftar sebagai Mitra
                            </button>
                        </form>

                        <p class="mt-8 text-center text-sm text-[#6a7282]">
                            Ingin belanja sebagai pelanggan?
                            <a href="{{ route('register') }}" class="font-bold text-[#00a63e] hover:underline">Daftar pelanggan</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
