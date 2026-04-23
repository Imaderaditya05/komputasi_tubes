<x-layouts.app :title="'Pengiriman (pelanggan) • ' . $restaurant->name">
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('mitra.restaurants.manage', $restaurant) }}" class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-[#00a63e] hover:underline">
                ← Kembali ke kelola restoran
            </a>

            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <h1 class="mb-2 text-2xl font-black text-gray-900">Pesanan delivery (checkout)</h1>
            <p class="mb-8 text-sm text-gray-600">
                Perbarui koordinat GPS kurir agar pelanggan melihat posisi realtime di halaman lacak pesanan.
                Gunakan titik dari aplikasi peta atau GPS perangkat kurir.
            </p>

            @if ($orders->isEmpty())
                <div class="rounded-2xl bg-white p-12 text-center shadow ring-1 ring-gray-100">
                    <p class="text-gray-600">Belum ada pesanan delivery dari menu mitra ini.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($orders as $o)
                        <article class="rounded-2xl bg-white p-5 shadow ring-1 ring-gray-100 sm:p-6">
                            <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Order ID</p>
                                    <p class="font-mono text-lg font-black text-[#00a63e]">{{ $o->public_order_id }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-800 ring-1 ring-slate-200">
                                    {{ $o->fulfillment_status ?? '—' }}
                                </span>
                            </div>
                            <p class="mb-4 text-sm text-gray-700"><span class="font-bold">Alamat:</span> {{ $o->delivery_address ?: '—' }}</p>
                            @if ($o->courier_updated_at)
                                <p class="mb-4 text-xs text-gray-500">Terakhir update kurir: {{ $o->courier_updated_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                            @endif

                            <form
                                action="{{ route('mitra.checkout-deliveries.courier', $restaurant) }}"
                                method="post"
                                class="flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4"
                            >
                                @csrf
                                <input type="hidden" name="public_order_id" value="{{ $o->public_order_id }}" />
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Latitude</label>
                                    <input
                                        type="text"
                                        name="latitude"
                                        inputmode="decimal"
                                        placeholder="-6.xxx"
                                        class="mt-1 w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                        value="{{ old('public_order_id') === $o->public_order_id ? old('latitude') : $o->courier_latitude }}"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Longitude</label>
                                    <input
                                        type="text"
                                        name="longitude"
                                        inputmode="decimal"
                                        placeholder="106.xxx"
                                        class="mt-1 w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                        value="{{ old('public_order_id') === $o->public_order_id ? old('longitude') : $o->courier_longitude }}"
                                        required
                                    />
                                </div>
                                <button type="submit" class="rounded-xl bg-[#00a63e] px-5 py-2 text-sm font-black text-white shadow hover:bg-[#008f36]">
                                    Kirim posisi kurir
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
