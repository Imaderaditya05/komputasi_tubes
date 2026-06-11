<x-layouts.app :title="'Order Berhasil • SurpriseBite'" variant="marketing">
    <x-checkout.modal-wrap>
        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/25 ring-1 ring-black/5">
            {{-- Header — Figma 94:804 --}}
            <div class="relative bg-[#00a63e] px-5 py-5 text-center sm:px-6 sm:py-6">
                <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}"
                   class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white ring-1 ring-white/25 transition hover:bg-white/25 sm:right-5 sm:top-5"
                   aria-label="Tutup">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
                <h2 class="flex items-center justify-center gap-2 pr-10 text-xl font-black text-white sm:text-2xl">
                    Order Berhasil! <x-sb.icon name="check-circle" class="h-7 w-7 shrink-0 text-white" />
                </h2>
            </div>

            {{-- Order summary --}}
            <div class="border-b border-emerald-100 bg-[#f0fdf4] px-5 py-5 ring-1 ring-inset ring-emerald-200/80 sm:px-6">
                @php
                    $checkoutQty = $order
                        ? max(1, (int) $order->item_quantity)
                        : max(1, (int) ($state['item_quantity'] ?? 1));
                    $unitPrice = (int) $box['price'];
                    $lineTotal = $order ? (int) $order->amount_idr : $unitPrice * $checkoutQty;
                @endphp
                <div class="flex items-center gap-2 text-sm font-black text-[#166534]">
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
                <div class="my-4 border-t border-emerald-200/90"></div>
                <div class="flex items-end justify-between">
                    <span class="text-sm font-black text-[#1e2939]">Total</span>
                    <span class="text-2xl font-black text-[#00a63e]">{{ $money($lineTotal) }}</span>
                </div>
            </div>

            <div class="px-5 py-6 sm:px-6">
                {{-- Pembayaran berhasil --}}
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#00a63e] text-white shadow-lg shadow-emerald-900/25">
                        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-xl font-black text-[#1e2939] sm:text-2xl">Pembayaran Berhasil!</h3>
                    <p class="mt-2 text-sm text-[#6a7282] sm:text-base">
                        Order ID: <span class="font-black text-[#00a63e]">{{ $state['order_id'] ?? 'ORD-000' }}</span>
                    </p>
                </div>

                {{-- Logistics --}}
                <div class="mt-8 space-y-4 rounded-2xl bg-[#f3f4f6] p-5 ring-1 ring-slate-200/80">
                    <div class="flex gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#dcfce7] text-[#00a63e]" aria-hidden="true"><x-sb.icon name="clock" class="h-5 w-5" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#6a7282]">Pickup Time</p>
                            <p class="mt-0.5 font-bold text-[#1e2939]">{{ $box['pickup_time'] }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#dcfce7] text-[#00a63e]" aria-hidden="true"><x-sb.icon name="map-pin" class="h-5 w-5" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#6a7282]">
                                {{ ($state['method'] ?? 'pickup') === 'delivery' ? 'Alamat Delivery' : 'Lokasi Pickup' }}
                            </p>
                            <p class="mt-0.5 break-words font-bold text-[#1e2939]">
                                @if (($state['method'] ?? 'pickup') === 'delivery')
                                    {{ trim((string) ($state['address'] ?? '')) !== '' ? $state['address'] : '—' }}
                                @else
                                    Ambil di {{ $restaurant['name'] }}, {{ $restaurant['area'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-8 grid grid-cols-2 gap-3">
                    <a href="{{ route('boxes.show', ['slug' => $box['slug']]) }}"
                       class="inline-flex items-center justify-center rounded-full border-2 border-slate-200 bg-white py-3.5 text-sm font-black text-[#1e2939] transition hover:bg-slate-50">
                        Tutup
                    </a>
                    <a href="{{ route('orders.index') }}"
                       class="inline-flex items-center justify-center rounded-full bg-[#00a63e] py-3.5 text-sm font-black text-white shadow-md shadow-emerald-900/20 transition hover:bg-[#008f3a]">
                        Riwayat pesanan
                    </a>
                </div>
            </div>
        </div>
    </x-checkout.modal-wrap>
</x-layouts.app>
