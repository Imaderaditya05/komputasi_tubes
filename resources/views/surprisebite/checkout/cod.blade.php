<x-layouts.app :title="'Bayar di tempat • SurpriseBite'" variant="marketing">
    <x-checkout.modal-wrap>
        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/25 ring-1 ring-black/5">
            <div class="relative bg-amber-500 px-5 py-5 text-center sm:px-6 sm:py-6">
                <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}"
                   class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white ring-1 ring-white/30 transition hover:bg-white/30 sm:right-5 sm:top-5"
                   aria-label="Tutup">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
                <h2 class="flex items-center justify-center gap-2 pr-10 text-xl font-black text-white sm:text-2xl">
                    Order COD <x-sb.icon name="document-text" class="h-6 w-6 shrink-0 text-white/95" />
                </h2>
                <p class="mt-1 text-sm font-bold text-amber-100">Bayar tunai saat ambil atau saat diantar</p>
            </div>

            <div class="border-b border-amber-100 bg-amber-50/90 px-5 py-5 ring-1 ring-inset ring-amber-200/80 sm:px-6">
                <div class="flex items-center gap-2 text-sm font-black text-amber-900">
                    <x-sb.icon name="package" class="h-5 w-5 shrink-0 text-amber-700" aria-hidden="true" />
                    Ringkasan order
                </div>
                <div class="mt-3 space-y-1.5 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-[#6a7282]">Restaurant</span>
                        <span class="font-bold text-[#1e2939]">{{ $restaurant['name'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-[#6a7282]">Mystery Box</span>
                        <span class="font-bold text-[#1e2939]">{{ $box['title'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-[#6a7282]">Pickup Time</span>
                        <span class="font-bold text-[#1e2939]">{{ $box['pickup_time'] }}</span>
                    </div>
                </div>
                <div class="my-4 border-t border-amber-200/90"></div>
                <div class="flex items-end justify-between">
                    <span class="text-sm font-black text-[#1e2939]">Total (dibayar di tempat)</span>
                    <span class="text-2xl font-black text-amber-600">{{ $money($box['price']) }}</span>
                </div>
            </div>

            <div class="px-5 py-6 sm:px-6">
                <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 text-sm font-bold text-amber-950">
                    <p class="font-black">Order ID: <span class="text-amber-700">{{ $state['order_id'] ?? '—' }}</span></p>
                    <p class="mt-2 text-xs font-semibold leading-relaxed text-amber-900/90">
                        Simpan Order ID ini. Siapkan uang pas (atau kembalian) sesuai total. Untuk pickup, bayar di lokasi restaurant;
                        untuk delivery, bayar ke kurir saat makanan tiba.
                    </p>
                </div>

                <div class="mt-6 space-y-4 rounded-2xl bg-[#f3f4f6] p-5 ring-1 ring-slate-200/80">
                    <div class="flex gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800" aria-hidden="true"><x-sb.icon name="map-pin" class="h-5 w-5" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#6a7282]">
                                {{ ($state['method'] ?? 'pickup') === 'delivery' ? 'Alamat delivery' : 'Lokasi pickup' }}
                            </p>
                            <p class="mt-0.5 break-words font-bold text-[#1e2939]">
                                @if (($state['method'] ?? 'pickup') === 'delivery')
                                    {{ trim((string) ($state['address'] ?? '')) !== '' ? $state['address'] : '—' }}
                                @else
                                    {{ $restaurant['name'] }}, {{ $restaurant['area'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-3">
                    <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}"
                       class="inline-flex items-center justify-center rounded-full border-2 border-slate-200 bg-white py-3.5 text-sm font-black text-[#1e2939] transition hover:bg-slate-50">
                        Tutup
                    </a>
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center justify-center rounded-full bg-[#00a63e] py-3.5 text-sm font-black text-white shadow-md shadow-emerald-900/20 transition hover:bg-[#008f3a]">
                        Ke beranda
                    </a>
                </div>
            </div>
        </div>
    </x-checkout.modal-wrap>
</x-layouts.app>
