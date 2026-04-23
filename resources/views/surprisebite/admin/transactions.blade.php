<x-layouts.admin title="Transaction monitoring" active="transactions">
    <div
        class="rounded-[24px] border-2 border-[#f3f4f6] bg-white p-6 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] sm:p-8"
        style="background-image: linear-gradient(141.254deg, rgb(249, 250, 251) 0%, rgba(240, 253, 244, 0.35) 100%);"
    >
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-base font-bold text-[#4a5565] hover:text-[#00a63e]">
            <span class="text-lg" aria-hidden="true">←</span>
            Back to Admin Dashboard
        </a>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#dcfce7] text-[#00a63e]" aria-hidden="true"><x-sb.icon name="banknotes" class="h-7 w-7" /></span>
            <div>
                <h2 class="text-3xl font-black tracking-tight text-[#1e2939] sm:text-4xl md:text-5xl">Transaction Monitoring</h2>
                <p class="mt-1 text-base font-semibold text-[#4a5565]">Monitor semua transaksi real-time</p>
            </div>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[24px] px-6 pb-6 pt-6 text-white shadow-[0_20px_25px_0px_rgba(0,0,0,0.1),0px_8px_10px_0px_rgba(0,0,0,0.1)]"
                 style="background-image: linear-gradient(156.56deg, rgb(0, 166, 62) 0%, rgb(0, 188, 125) 100%);">
                <p class="text-sm font-bold text-[#dcfce7]">Total Revenue</p>
                <p class="mt-2 text-4xl font-black" id="rt-trx-revenue">{{ $moneyShort($summary['revenue_idr']) }}</p>
            </div>
            <div class="rounded-2xl border-2 border-[#dcfce7] bg-white px-6 pb-6 pt-6 shadow-[0_10px_15px_0px_rgba(0,0,0,0.1),0px_4px_6px_0px_rgba(0,0,0,0.1)]">
                <div class="flex items-center gap-3">
                    <x-sb.icon name="check-circle" class="h-8 w-8 shrink-0 text-[#00a63e]" aria-hidden="true" />
                    <span class="text-sm font-bold text-[#4a5565]">Completed</span>
                </div>
                <p class="mt-2 text-4xl font-black text-[#00a63e]" id="rt-trx-completed">{{ number_format($summary['completed']) }}</p>
            </div>
            <div class="rounded-2xl border-2 border-black/10 bg-white px-6 pb-6 pt-6 shadow-[0_10px_15px_0px_rgba(0,0,0,0.1),0px_4px_6px_0px_rgba(0,0,0,0.1)]">
                <div class="flex items-center gap-3">
                    <x-sb.icon name="clock" class="h-8 w-8 shrink-0 text-[#f54900]" aria-hidden="true" />
                    <span class="text-sm font-bold text-[#4a5565]">Pending</span>
                </div>
                <p class="mt-2 text-4xl font-black text-[#f54900]" id="rt-trx-pending">{{ number_format($summary['pending']) }}</p>
            </div>
            <div class="rounded-2xl border-2 border-black/10 bg-white px-6 pb-6 pt-6 shadow-[0_10px_15px_0px_rgba(0,0,0,0.1),0px_4px_6px_0px_rgba(0,0,0,0.1)]">
                <div class="flex items-center gap-3">
                    <x-sb.icon name="x-circle" class="h-8 w-8 shrink-0 text-[#e7000b]" aria-hidden="true" />
                    <span class="text-sm font-bold text-[#4a5565]">Failed</span>
                </div>
                <p class="mt-2 text-4xl font-black text-[#e7000b]" id="rt-trx-failed">{{ number_format($summary['failed']) }}</p>
            </div>
        </div>

        <form method="get" action="{{ route('admin.transactions') }}" class="mt-8 flex flex-col gap-4 rounded-2xl border-2 border-[#f3f4f6] bg-white p-4 shadow-[0_10px_15px_0px_rgba(0,0,0,0.1),0px_4px_6px_0px_rgba(0,0,0,0.1)] lg:flex-row lg:items-center lg:gap-4">
            <div class="relative min-w-0 flex-1">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"><x-sb.icon name="search" class="h-5 w-5" /></span>
                <input type="search" name="q" value="{{ $search }}"
                       placeholder="Search by ID, order, customer, or restaurant..."
                       class="w-full rounded-[14px] border-2 border-[#e5e7eb] py-3 pl-12 pr-4 text-base font-semibold text-[#1e2939] placeholder:text-[#71717a]/70 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25" />
            </div>
            <div class="flex flex-wrap items-center gap-3 lg:shrink-0">
                <select name="status"
                        class="min-w-[140px] rounded-[14px] border-2 border-[#e5e7eb] bg-white px-4 py-3 text-sm font-bold text-[#364153] focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                        onchange="this.form.submit()">
                    <option value="" @selected($statusFilter === null)>Semua status</option>
                    <option value="completed" @selected($statusFilter === 'completed')>Completed</option>
                    <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
                    <option value="failed" @selected($statusFilter === 'failed')>Failed</option>
                </select>
                <button type="submit" class="hidden sm:inline-flex rounded-[14px] bg-[#f3f4f6] px-5 py-3 text-sm font-bold text-[#364153] hover:bg-[#e5e7eb]">Cari</button>
                <a href="{{ route('admin.transactions', array_filter(['q' => $search, 'status' => $statusFilter, 'export' => 1])) }}"
                   class="inline-flex items-center gap-2 rounded-[14px] bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-5 py-3 text-base font-bold text-white shadow-md hover:opacity-95">
                    <x-sb.icon name="arrow-down-tray" class="h-5 w-5" aria-hidden="true" />
                    Export
                </a>
            </div>
        </form>

        <div class="mt-8 overflow-hidden rounded-[24px] border-2 border-[#f3f4f6] bg-white shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)]">
            <div class="overflow-x-auto">
                <table class="min-w-[1100px] w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#00a63e] to-[#00bc7d] text-white">
                            <th class="px-4 py-5 text-base font-black">Transaction ID</th>
                            <th class="px-4 py-5 text-base font-black">Order ID</th>
                            <th class="px-4 py-5 text-base font-black">Customer</th>
                            <th class="px-4 py-5 text-base font-black">Restaurant</th>
                            <th class="px-4 py-5 text-base font-black">Amount</th>
                            <th class="px-4 py-5 text-base font-black">Payment</th>
                            <th class="px-4 py-5 text-base font-black">Status</th>
                            <th class="px-4 py-5 text-base font-black">Date &amp; Time</th>
                            <th class="px-4 py-5 text-base font-black">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rt-transactions-tbody">
                        @include('surprisebite.admin.partials.transactions-tbody', ['orders' => $orders, 'money' => $money])
                    </tbody>
                </table>
            </div>
        </div>

        @if ($orders->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
