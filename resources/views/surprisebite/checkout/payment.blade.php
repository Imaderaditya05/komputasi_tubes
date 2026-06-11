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
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#00a63e] text-white" title="Selesai">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span class="text-[#6a7282]">Delivery</span>
                    <span class="h-0.5 min-w-[2rem] flex-1 rounded-full bg-[#00a63e] sm:min-w-[4rem]"></span>
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-[#00a63e] bg-white text-[#00a63e]">2</span>
                    <span class="text-[#00a63e]">Payment</span>
                </div>
            </div>

            <div class="border-b border-slate-200 bg-white px-5 py-5 sm:px-6">
                @php
                    $checkoutQty = max(1, (int) ($state['item_quantity'] ?? 1));
                    $unitPrice = (int) $box['price'];
                    $lineTotal = $unitPrice * $checkoutQty;
                @endphp
                <div class="flex items-center gap-2 text-sm font-black text-[#1e2939]">
                    <x-sb.icon name="package" class="h-5 w-5 shrink-0 text-[#00a63e]" aria-hidden="true" />
                    Order Summary
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
                        <span class="text-[#6a7282]">Jumlah</span>
                        <span class="font-bold text-[#1e2939]">{{ $checkoutQty }} × {{ $money($unitPrice) }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-[#6a7282]">Pickup Time</span>
                        <span class="font-bold text-[#1e2939]">{{ $box['pickup_time'] }}</span>
                    </div>
                </div>
                <div class="my-4 border-t border-slate-200"></div>
                <div class="flex items-end justify-between">
                    <span class="text-sm font-black text-[#1e2939]">Total</span>
                    <span class="text-2xl font-black text-[#00a63e]">{{ $money($lineTotal) }}</span>
                </div>
            </div>

            <form method="post" action="{{ route('checkout.pay', ['slug' => $box['slug']]) }}" class="sb-checkout-pay space-y-4 p-5 sm:p-6">
                @csrf

                @if (session('error'))
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-800" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="text-sm font-black text-[#1e2939]">Pilih Metode Pembayaran</p>

                @php $current = old('payment', $state['payment']); @endphp

                <label class="block cursor-pointer">
                    <input type="radio" name="payment" value="va" class="sr-only" @checked($current === 'va') />
                    <div class="flex items-center justify-between gap-3 rounded-2xl border-2 border-slate-200 p-4 transition hover:border-slate-300">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[#00a63e] ring-1 ring-slate-200"><x-sb.icon name="bank" class="h-6 w-6" /></span>
                            <div>
                                <div class="font-black text-[#1e2939]">Virtual Account</div>
                                <div class="text-xs font-medium text-[#6a7282]">Transfer VA &amp; metode lain via Midtrans</div>
                            </div>
                        </div>
                        <span class="sb-pay-check flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-slate-200 text-white" aria-hidden="true"><x-sb.icon name="check" class="h-4 w-4" /></span>
                    </div>
                </label>

                <label class="block cursor-pointer">
                    <input type="radio" name="payment" value="cod" class="sr-only" @checked($current === 'cod') />
                    <div class="flex items-center justify-between gap-3 rounded-2xl border-2 border-slate-200 p-4 transition hover:border-slate-300">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[#00a63e] ring-1 ring-slate-200"><x-sb.icon name="banknotes" class="h-6 w-6" /></span>
                            <div>
                                <div class="font-black text-[#1e2939]">Bayar di tempat</div>
                                <div class="text-xs font-medium text-[#6a7282]">Tunai saat ambil / saat diantar</div>
                            </div>
                        </div>
                        <span class="sb-pay-check flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-slate-200 text-white" aria-hidden="true"><x-sb.icon name="check" class="h-4 w-4" /></span>
                    </div>
                </label>

                <style>
                    .sb-checkout-pay input[type="radio"]:checked + div {
                        border-color: #00a63e;
                        background-color: #f0fdf4;
                    }
                    .sb-checkout-pay .sb-pay-check {
                        opacity: 0;
                    }
                    .sb-checkout-pay input[type="radio"]:checked + div .sb-pay-check {
                        opacity: 1;
                        border-color: #00a63e;
                        background-color: #00a63e;
                    }
                </style>

                @error('payment')
                    <div class="text-sm font-bold text-red-600">{{ $message }}</div>
                @enderror

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between">
                    <a href="{{ route('checkout.delivery', ['slug' => $box['slug']]) }}"
                       class="inline-flex flex-1 items-center justify-center rounded-full border-2 border-slate-200 bg-white py-3 text-sm font-black text-[#364153] transition hover:bg-slate-50 sm:flex-none sm:px-8">
                        Kembali
                    </a>
                    <button type="submit"
                            class="inline-flex flex-1 items-center justify-center rounded-full bg-[#00a63e] py-3 text-sm font-black text-white shadow-md shadow-emerald-900/20 transition hover:bg-[#008f3a] sm:flex-none sm:min-w-[160px]">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </x-checkout.modal-wrap>
</x-layouts.app>
