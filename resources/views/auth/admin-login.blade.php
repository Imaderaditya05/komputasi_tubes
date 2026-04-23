<x-layouts.app :title="'Admin Login • SurpriseBite'" variant="marketing">
    <div class="pb-16 pt-6 sm:pt-10">
        <div class="mx-auto max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/10 ring-1 ring-black/5">
            <div class="bg-gradient-to-r from-[#14532d] to-[#00a63e] px-8 py-8 text-center text-white">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white p-2 ring-4 ring-white/20 shadow-lg transition hover:scale-105">
                    <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="SurpriseBite Logo" class="h-full w-full object-contain" />
                </div>
                <h1 class="mt-4 text-2xl font-black">Admin</h1>
                <p class="mt-1 text-sm text-white/85">Masuk ke panel SurpriseBite</p>
            </div>
            <div class="px-8 py-8 sm:px-10">
                <form method="post" action="{{ route('login.admin.submit') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="text-sm font-bold text-[#1e2939]">Email admin</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                               class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25" />
                        @error('email')
                            <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="text-sm font-bold text-[#1e2939]">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3.5 text-sm focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25" />
                    </div>
                    <button type="submit" class="w-full rounded-full bg-[#14532d] py-3.5 text-sm font-black text-white shadow-md transition hover:bg-[#166534]">
                        Masuk sebagai admin
                    </button>
                </form>
                <p class="mt-6 text-center text-xs text-[#6a7282]">
                    <a href="{{ route('login') }}" class="font-bold text-[#00a63e] hover:underline">← Kembali ke login pelanggan</a>
                </p>
                <p class="mt-4 rounded-2xl bg-slate-50 p-3 text-center text-[10px] text-slate-500 ring-1 ring-slate-100">
                    Demo: <span class="font-semibold text-slate-700">admin@surprisebite.test</span> / <span class="font-semibold">password</span>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
