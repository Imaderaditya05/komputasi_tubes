<x-layouts.app :title="'Checkout • SurpriseBite'" variant="marketing">
    <x-checkout.modal-wrap>
        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/25 ring-1 ring-black/5">
            <div class="flex items-center justify-between bg-[#00a63e] px-5 py-4 sm:px-6">
                <h2 class="text-xl font-black text-white sm:text-2xl">Checkout</h2>
                <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}"
                   class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white ring-1 ring-white/25 transition hover:bg-white/25"
                   aria-label="Tutup">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>

            <div class="border-b border-slate-100 bg-white px-5 py-5 sm:px-6">
                <div class="flex items-center gap-2 text-sm font-bold">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#00a63e] text-white">1</span>
                    <span class="text-[#00a63e]">Delivery</span>
                    <span class="h-0.5 min-w-[2rem] flex-1 rounded-full bg-[#00a63e] sm:min-w-[4rem]"></span>
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#dcfce7] text-sm text-[#166534] ring-1 ring-[#bbf7d0]">2</span>
                    <span class="text-[#6a7282]">Payment</span>
                </div>
            </div>

            <div class="border-b border-emerald-100 bg-[#f0fdf4] px-5 py-5 ring-1 ring-inset ring-emerald-200/80 sm:px-6">
                @php
                    $checkoutQty = max(1, (int) ($state['item_quantity'] ?? 1));
                    $unitPrice = (int) $box['price'];
                    $lineTotal = $unitPrice * $checkoutQty;
                @endphp
                <div class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
                    <div>
                        <div class="text-sm font-bold text-[#166534]">Ringkasan pesanan</div>
                        <div class="mt-3 space-y-1.5 text-sm">
                            <div class="flex justify-between gap-4 text-[#364153]">
                                <span class="text-[#6a7282]">Restaurant</span>
                                <span class="font-bold">{{ $restaurant['name'] }}</span>
                            </div>
                            <div class="flex justify-between gap-4 text-[#364153]">
                                <span class="text-[#6a7282]">Mystery Box</span>
                                <span class="font-bold">{{ $box['title'] }}</span>
                            </div>
                            <div class="flex justify-between gap-4 text-[#364153]">
                                <span class="text-[#6a7282]">Jumlah</span>
                                <span class="font-bold">{{ $checkoutQty }} × {{ $money($unitPrice) }}</span>
                            </div>
                            <div class="flex justify-between gap-4 text-[#364153]">
                                <span class="text-[#6a7282]">Pickup Time</span>
                                <span class="font-bold">{{ $box['pickup_time'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-emerald-200/80 pt-4 sm:border-0 sm:pt-0 sm:text-right">
                        <div class="text-xs font-bold uppercase tracking-wide text-[#6a7282]">Total</div>
                        <div class="text-2xl font-black text-[#00a63e]">{{ $money($lineTotal) }}</div>
                    </div>
                </div>
            </div>

            <form method="post" action="{{ route('checkout.delivery.submit', ['slug' => $box['slug']]) }}" class="space-y-6 p-5 sm:p-6">
                @csrf

                <div>
                    <p class="text-sm font-black text-[#1e2939]">Pilih Metode Pengambilan</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="block cursor-pointer">
                            <input type="radio" name="method" value="pickup" class="peer sr-only" @checked(old('method', $state['method']) === 'pickup') />
                            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 transition peer-checked:border-[#00a63e] peer-checked:bg-[#f0fdf4] hover:border-slate-300">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-[#00a63e] ring-1 ring-slate-200"><x-sb.icon name="map-pin" class="h-5 w-5" /></span>
                                    <div>
                                        <div class="font-black text-[#1e2939]">Pickup</div>
                                        <div class="mt-0.5 text-xs font-medium text-[#6a7282]">Ambil di restoran</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <label class="block cursor-pointer">
                            <input type="radio" name="method" value="delivery" class="peer sr-only" @checked(old('method', $state['method']) === 'delivery') />
                            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 transition peer-checked:border-[#00a63e] peer-checked:bg-[#f0fdf4] hover:border-slate-300">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-[#00a63e] ring-1 ring-slate-200"><x-sb.icon name="package" class="h-5 w-5" /></span>
                                    <div>
                                        <div class="font-black text-[#1e2939]">Delivery</div>
                                        <div class="mt-0.5 text-xs font-medium text-[#6a7282]">Diantar ke lokasi</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="address" class="text-sm font-black text-[#1e2939]">Alamat Pengiriman</label>
                        @error('address')
                            <span class="text-xs font-bold text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <textarea id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap..."
                              class="mt-2 w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] placeholder:text-slate-400 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/30">{{ old('address', $state['address']) }}</textarea>
                    <p class="mt-1.5 text-xs text-[#6a7282]">Wajib diisi jika memilih Delivery.</p>
                </div>

                <button type="submit"
                        class="w-full rounded-full bg-[#4ade80] py-3.5 text-center text-base font-black text-white shadow-md shadow-emerald-900/15 transition hover:bg-[#22c55e]">
                    Lanjut ke Pembayaran &gt;
                </button>
                <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}"
                   class="block text-center text-sm font-bold text-[#6a7282] hover:text-[#00a63e]">
                    Kembali
                </a>
            </form>
        </div>
    </x-checkout.modal-wrap>
</x-layouts.app>
