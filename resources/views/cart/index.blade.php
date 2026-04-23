<x-layouts.app :title="'Keranjang Belanja'" variant="marketing">
<div class="pb-16 pt-6 sm:pt-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="flex items-center gap-3 text-3xl font-black text-[#1e2939] sm:text-4xl md:text-5xl">
            <svg class="h-8 w-8 sm:h-10 sm:w-10 text-[#ff6900]" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            Keranjang Belanja
        </h1>
        <p class="mt-2 text-base text-[#6a7282] sm:text-lg">Tinjau dan kelola mystery boxes pilihan Anda</p>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-bold text-emerald-900">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Error Message -->
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 shrink-0 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="font-bold text-red-800">Terjadi kesalahan</h3>
                    <ul class="mt-2 text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Cart Empty State -->
    @if ($isEmpty)
        <div class="rounded-3xl bg-white px-8 py-16 text-center shadow-md ring-1 ring-slate-100">
            <div class="text-6xl mb-4">📦</div>
            <h2 class="text-2xl font-black text-[#1e2939] mb-3">Keranjang Anda Kosong</h2>
            <p class="text-[#6a7282] mb-8">Mulai berbelanja mystery boxes eksklusif kami sekarang!</p>
            <a href="{{ route('browse') }}" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#ff6900] to-[#f54900] px-8 py-3 text-base font-bold text-white shadow-lg hover:shadow-xl transition">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.5 1.5H3a1.5 1.5 0 00-1.5 1.5v14a1.5 1.5 0 001.5 1.5h14a1.5 1.5 0 001.5-1.5V9.5" stroke="currentColor" stroke-width="1.5" fill="none"/>
                </svg>
                Jelajahi Mystery Boxes
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Items List -->
            <div class="lg:col-span-2">
                <div class="rounded-3xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden">
                    <!-- Table Header -->
                    <div class="hidden md:grid grid-cols-12 bg-[#f3f4f6] border-b border-slate-200 px-6 py-4 font-bold text-[#364153] text-sm">
                        <div class="col-span-5">Produk</div>
                        <div class="col-span-2 text-center">Harga</div>
                        <div class="col-span-2 text-center">Qty</div>
                        <div class="col-span-2 text-center">Subtotal</div>
                        <div class="col-span-1 text-center">Aksi</div>
                    </div>

                    <!-- Items -->
                    @forelse ($items as $item)
                        <div class="border-b border-slate-200 last:border-b-0">
                            <!-- Desktop View -->
                            <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-[#f9fafb] transition">
                                <!-- Product Info -->
                                <div class="col-span-5">
                                    <h3 class="font-bold text-[#1e2939]">{{ $item->box_title }}</h3>
                                    <p class="text-sm text-[#6a7282] mt-1">
                                        📍 {{ $item->restaurant_name }}
                                    </p>
                                </div>

                                <!-- Price -->
                                <div class="col-span-2 text-center">
                                    <span class="font-bold text-[#1e2939]">{{ $item->getFormattedPrice() }}</span>
                                </div>

                                <!-- Quantity Control -->
                                <div class="col-span-2">
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ max(0, $item->quantity - 1) }}">
                                            <button type="submit" class="text-[#6a7282] hover:text-[#00a63e] transition">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                        <span class="w-10 text-center font-bold text-[#1e2939]">{{ $item->quantity }}</span>
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                            <button type="submit" class="text-[#6a7282] hover:text-[#ff6900] transition" @if ($item->quantity >= $item->stock_available) disabled @endif>
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    <p class="text-xs text-[#6a7282] text-center mt-2">Stok: {{ $item->stock_available }}</p>
                                </div>

                                <!-- Subtotal -->
                                <div class="col-span-2 text-center">
                                    <span class="font-bold text-[#00a63e]">{{ $item->getFormattedSubtotal() }}</span>
                                </div>

                                <!-- Delete Button -->
                                <div class="col-span-1 flex justify-center">
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#ff6900] hover:text-red-700 transition" onclick="return confirm('Hapus item ini?')">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Mobile View -->
                            <div class="md:hidden px-4 py-4 space-y-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-bold text-[#1e2939]">{{ $item->box_title }}</h3>
                                        <p class="text-sm text-[#6a7282]">{{ $item->restaurant_name }}</p>
                                    </div>
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#ff6900] hover:text-red-700" onclick="return confirm('Hapus?')">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold">{{ $item->getFormattedPrice() }} × {{ $item->quantity }}</span>
                                    <span class="text-sm font-bold text-[#ff6900]">{{ $item->getFormattedSubtotal() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-[#6a7282]">
                            Tidak ada item dalam keranjang
                        </div>
                    @endforelse
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex gap-3 flex-col sm:flex-row">
                    <a href="{{ route('browse') }}" class="flex-1 rounded-full border-2 border-[#00a63e] bg-white text-[#00a63e] font-bold py-3 px-6 hover:bg-[#f0fdf4] transition text-center">
                        ← Lanjut Belanja
                    </a>
                    <form action="{{ route('cart.clear') }}" method="POST" class="inline flex-1">
                        @csrf
                        <button type="submit" class="w-full rounded-full bg-[#f3f4f6] hover:bg-[#e5e7eb] text-[#364153] font-bold py-3 px-6 transition" onclick="return confirm('Kosongkan semua item?')">
                            🗑️ Kosongkan Cart
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="sticky top-4 rounded-3xl bg-white shadow-md ring-1 ring-slate-100 p-6">
                    <h2 class="text-lg font-black text-[#1e2939] mb-6 flex items-center gap-2">
                        <span>📋</span> Ringkasan Pesanan
                    </h2>

                    <div class="space-y-4 mb-6 pb-6 border-b border-slate-200">
                        <div class="flex justify-between text-[#6a7282]">
                            <span>Jumlah Item</span>
                            <span class="font-bold text-[#1e2939]">{{ $totalQuantity }} item</span>
                        </div>
                        <div class="flex justify-between text-[#6a7282]">
                            <span>Restoran</span>
                            <span class="font-bold text-[#1e2939]">{{ count($restaurants) }}</span>
                        </div>

                        @if (count($restaurants) > 1)
                            <div class="rounded-2xl bg-[#fef3c7] border border-[#fcd34d] p-3 text-xs text-[#92400e]">
                                <strong>⚠️ Catatan:</strong> Checkout hanya untuk 1 restoran. Silakan pisahkan item.
                            </div>
                        @endif
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between mb-4">
                            <span class="text-[#6a7282]">Subtotal</span>
                            <span class="font-bold text-[#1e2939]">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-slate-200 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-[#1e2939]">Total</span>
                                <span class="text-3xl font-black text-[#ff6900]">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('checkout.delivery', ['slug' => $items->first()?->box_slug ?? '']) }}" method="GET" class="block mb-4">
                        <button type="submit" class="w-full rounded-full bg-gradient-to-r from-[#ff6900] to-[#f54900] hover:shadow-lg text-white font-bold py-3 px-6 shadow-md transition" @if(count($restaurants) > 1) disabled @endif>
                            Lanjut ke Checkout →
                        </button>
                    </form>

                    <div class="text-xs text-[#6a7282] text-center space-y-1">
                        <p>✓ Data tersimpan aman</p>
                        <p>✓ Pembayaran terenkripsi</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</x-layouts.app>
