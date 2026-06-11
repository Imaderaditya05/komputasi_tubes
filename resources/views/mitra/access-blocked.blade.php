@php
    $hasPendingAppeal = $lastAppeal && $lastAppeal->status === 'pending';
@endphp

<x-layouts.app title="Akses Restoran — SurpriseBite" variant="marketing" :mitra-store-name="$restaurant->name">
    <div class="min-h-[calc(100vh-5rem)] bg-[#f4f6f8] pb-16 pt-10">
        <div class="mx-auto max-w-lg px-4">
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('access'))
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900 ring-1 ring-red-100">
                    {{ $errors->first('access') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl bg-white shadow-xl shadow-black/10 ring-1 ring-black/5">
                <div class="bg-[#0f172a] px-6 py-8 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-2 ring-white/20">
                        <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h1 class="text-xl font-black text-white sm:text-2xl">Akses Restoran</h1>
                    <p class="mt-2 text-sm font-semibold text-slate-400">{{ $restaurant->name }}</p>
                </div>

                <div class="px-6 py-8 sm:px-8">
                    <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 ring-8 ring-rose-50">
                        <svg class="h-9 w-9 text-rose-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <h2 class="text-center text-xl font-black text-[#1e2939]">Akses Diblokir</h2>
                    <p class="mt-4 text-center text-sm font-medium leading-relaxed text-[#6a7282]">
                        Akses ke restoran Anda telah ditahan oleh sistem atau admin. Silakan hubungi layanan bantuan atau admin untuk menyelesaikan masalah ini.
                        Anda juga dapat mengajukan banding dengan menjelaskan alasan di bawah.
                    </p>

                    @if ($hasPendingAppeal)
                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-950 ring-1 ring-amber-100">
                            Pengajuan banding terakhir ({{ $lastAppeal->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}) masih <strong>menunggu tinjauan admin</strong>.
                        </div>
                    @elseif ($lastAppeal && $lastAppeal->status !== 'pending')
                        <p class="mt-4 text-center text-xs text-slate-500">
                            Pengajuan sebelumnya: <span class="font-bold capitalize">{{ str_replace('_', ' ', $lastAppeal->status) }}</span>
                            — {{ $lastAppeal->updated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                        </p>
                    @endif

                    @if (! $hasPendingAppeal)
                        <form method="post" action="{{ route('mitra.restaurants.access-appeal', $restaurant) }}" class="mt-8 space-y-4">
                            @csrf
                            <div>
                                <label for="appeal-message" class="text-sm font-black text-[#1e2939]">Ajukan banding — jelaskan alasan</label>
                                <textarea id="appeal-message" name="message" rows="5" required minlength="20" maxlength="5000"
                                    placeholder="Contoh: kami telah memperbaiki dokumentasi yang diminta, siap verifikasi ulang…"
                                    class="mt-2 w-full resize-y rounded-2xl border border-slate-200 bg-[#fafafa] px-4 py-3 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25">{{ old('message') }}</textarea>
                                <p class="mt-1 text-xs font-medium text-[#6a7282]">Minimal 20 karakter. Pesan akan diterima oleh admin.</p>
                                @error('message')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                class="w-full rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] py-3.5 text-base font-black text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-105">
                                Kirim pengajuan banding
                            </button>
                        </form>
                    @endif

                    <form method="post" action="{{ route('logout') }}" class="mt-8">
                        @csrf
                        <button type="submit" class="w-full rounded-2xl border-2 border-slate-200 bg-white py-3 text-sm font-black text-[#364153] transition hover:bg-slate-50">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
