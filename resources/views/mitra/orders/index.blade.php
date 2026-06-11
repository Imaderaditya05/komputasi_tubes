<x-layouts.app :title="'Pesanan Checkout — ' . $restaurant->name">
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
                <div>
                    <a href="{{ route('mitra.restaurants.manage', $restaurant) }}" class="text-sm font-bold text-emerald-700 hover:underline">← Kembali kelola restoran</a>
                    <h2 class="text-3xl font-black text-gray-900 mt-2">Pesanan checkout</h2>
                    <p class="mt-2 text-gray-600">Semua mystery box untuk restoran ini (alur pembayaran &amp; fulfillment terpusat).</p>
                </div>
                <span class="text-sm font-bold text-gray-500 rounded-full bg-white px-4 py-2 shadow ring-1 ring-gray-100">{{ $orders->count() }} tiket ditampilkan</span>
            </div>

            <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-100 text-xs font-black uppercase tracking-wider text-gray-600">
                            <tr>
                                <th class="px-4 py-3">ID Pesanan</th>
                                <th class="px-4 py-3">Menu</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Pembayaran</th>
                                <th class="px-4 py-3">Status mitra</th>
                                <th class="px-4 py-3">Fulfillment</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($orders as $o)
                                @php
                                    $phase = \App\Services\MitraCheckoutOrderStatusService::lifecyclePhase($o);
                                    $phaseLabel = match ($phase) {
                                        'completed' => 'Selesai',
                                        'paid' => 'Lunas / diproses',
                                        default => 'Menunggu pembayaran',
                                    };
                                    $phaseClass = match ($phase) {
                                        'completed' => 'bg-emerald-100 text-emerald-900 ring-emerald-200',
                                        'paid' => 'bg-blue-50 text-blue-900 ring-blue-200',
                                        default => 'bg-amber-50 text-amber-900 ring-amber-200',
                                    };
                                    $payHuman = match ((string) ($o->payment_status ?? '')) {
                                        'PAID' => 'Lunas',
                                        'PENDING_COD' => 'COD — tunggu',
                                        'PENDING' => 'Menunggu (online)',
                                        default => $o->payment_status ?? '—',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/80 align-top">
                                    <td class="px-4 py-3 font-mono font-bold text-gray-900 whitespace-nowrap">{{ $o->public_order_id }}</td>
                                    <td class="px-4 py-3 text-gray-800 max-w-[10rem] sm:max-w-none">{{ $o->box_title ?? $o->box_slug }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-xs">{{ $o->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3 text-xs font-semibold text-gray-700">{{ $payHuman }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-black ring-1 {{ $phaseClass }}">{{ $phaseLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-700">{{ str_replace('_', ' ', $o->fulfillment_status ?? '—') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('restaurants.orders.show', [$restaurant, $o->public_order_id]) }}" class="text-emerald-700 font-bold hover:underline">Kelola</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-600">Belum ada pesanan checkout dari menu restoran Anda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
