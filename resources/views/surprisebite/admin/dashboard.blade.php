<x-layouts.admin title="Dashboard" active="dashboard">
    <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-xl shadow-slate-200/40 sm:p-8">
        {{-- Header Section --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#00a63e]">Ringkasan Hari Ini</p>
                <h2 class="mt-2 flex items-center gap-3 text-3xl font-black tracking-tight text-slate-900">
                    Hai, {{ $auth['name'] ?? 'Admin' }}! <span class="animate-bounce inline-block origin-bottom">👋</span>
                </h2>
                <p class="mt-2 text-sm text-slate-500">Semoga harimu menyenangkan! Mari pantau dampak positif kita hari ini.</p>
            </div>
            <div class="text-right hidden sm:block">
                <p class="text-sm font-semibold text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50 p-6 shadow-sm transition hover:shadow-lg hover:shadow-slate-200/50">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-blue-50 transition group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 text-sm font-bold text-slate-500">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <x-sb.icon name="users" class="h-4 w-4" />
                        </div>
                        Total Pengguna
                    </div>
                    <div class="mt-4 text-4xl font-black text-slate-900" id="rt-stat-customers">{{ number_format($totalCustomers) }}</div>
                    <div class="mt-2 text-xs font-medium text-slate-500">Pelanggan pahlawan sisa makanan</div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50 p-6 shadow-sm transition hover:shadow-lg hover:shadow-slate-200/50">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-orange-50 transition group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 text-sm font-bold text-slate-500">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                            <x-sb.icon name="credit-card" class="h-4 w-4" />
                        </div>
                        Total Transaksi
                    </div>
                    <div class="mt-4 text-4xl font-black text-slate-900" id="rt-stat-transactions">{{ number_format($totalTransactions) }}</div>
                    <div class="mt-2 text-xs font-medium text-slate-500">Penjualan berhasil keseluruhan</div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm transition hover:shadow-lg hover:shadow-emerald-200/40">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-100/50 transition group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 text-sm font-bold text-emerald-800">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-200 text-emerald-700">
                            <x-sb.icon name="package" class="h-4 w-4" />
                        </div>
                        Pesanan Hari Ini
                    </div>
                    <div class="mt-4 text-4xl font-black text-emerald-900" id="rt-stat-orders-today">{{ number_format($ordersToday) }}</div>
                    <div class="mt-2 text-xs font-bold text-emerald-600">Makanan diselamatkan hari ini!</div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-[#00a63e] to-[#00bc7d] p-6 shadow-md shadow-emerald-900/20 transition hover:shadow-lg hover:shadow-emerald-900/40">
                <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/10 transition group-hover:scale-110 blur-xl"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 text-sm font-bold text-emerald-50">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-white">
                            <x-sb.icon name="chart-bar" class="h-4 w-4" />
                        </div>
                        Pendapatan Hari Ini
                    </div>
                    <div class="mt-4 text-3xl font-black tracking-tight text-white sm:text-4xl leading-none" id="rt-stat-revenue-today">
                        {{ $money($revenueToday) }}
                    </div>
                    <div class="mt-2 text-xs font-semibold text-emerald-100">Nilai transaksi terselesaikan hari ini</div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="mt-12">
            <h3 class="text-lg font-black text-slate-900">Akses Cepat</h3>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('admin.users') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#1e40af] to-[#1e3a8a] p-6 text-white shadow-lg transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-900/20">
                    <div class="absolute -right-4 -top-4 opacity-10 transition group-hover:scale-125"><x-sb.icon name="users" class="h-24 w-24" /></div>
                    <div class="relative">
                        <div class="text-lg font-black">User Management</div>
                        <div class="mt-1 text-xs font-medium text-blue-100/80">Kelola Pelanggan & Seller</div>
                    </div>
                </a>
                <a href="{{ route('admin.transactions') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#059669] to-[#047857] p-6 text-white shadow-lg transition hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-900/20">
                    <div class="absolute -right-4 -top-4 opacity-10 transition group-hover:scale-125"><x-sb.icon name="credit-card" class="h-24 w-24" /></div>
                    <div class="relative">
                        <div class="text-lg font-black">Transactions</div>
                        <div class="mt-1 text-xs font-medium text-emerald-100/80">Pantau transaksi real-time</div>
                    </div>
                </a>
                <a href="{{ route('admin.restaurants') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#f97316] to-[#ea580c] p-6 text-white shadow-lg transition hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-900/20">
                    <div class="absolute -right-4 -top-4 opacity-10 transition group-hover:scale-125"><x-sb.icon name="package" class="h-24 w-24" /></div>
                    <div class="relative">
                        <div class="text-lg font-black">Restaurants</div>
                        <div class="mt-1 text-xs font-medium text-orange-100/80">Kelola menu & mystery box</div>
                    </div>
                </a>
                <a href="{{ route('admin.impact') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#8b5cf6] to-[#7c3aed] p-6 text-white shadow-lg transition hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-900/20">
                    <div class="absolute -right-4 -top-4 opacity-10 transition group-hover:scale-125"><x-sb.icon name="globe" class="h-24 w-24" /></div>
                    <div class="relative">
                        <div class="text-lg font-black">Impact Tracker</div>
                        <div class="mt-1 text-xs font-medium text-violet-100/80">Pantau pengurangan emisi</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="mt-12 rounded-3xl border border-slate-100 bg-slate-50/50 p-6 sm:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Aktivitas Terbaru</h3>
                    <p class="mt-1 text-xs text-slate-500">Live feed pesanan pelanggan terbaru.</p>
                </div>
                <a href="{{ route('admin.transactions') }}" class="hidden text-sm font-bold text-[#00a63e] hover:underline sm:block">Lihat Semua →</a>
            </div>
            
            <div class="mt-6 space-y-4 text-sm" id="rt-recent-orders">
                @include('surprisebite.admin.partials.recent-orders-feed', [
                    'recentOrders' => $recentOrders,
                    'money' => $money,
                    'paymentLabel' => $paymentLabel,
                ])
            </div>
            <a href="{{ route('admin.transactions') }}" class="mt-6 block text-center text-sm font-bold text-[#00a63e] hover:underline sm:hidden">Lihat Semua Transaksi</a>
        </div>
    </div>
</x-layouts.admin>
