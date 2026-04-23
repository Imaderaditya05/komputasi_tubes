@forelse ($recentOrders as $order)
    <div class="group flex items-start gap-4 rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm transition hover:border-[#00a63e]/30 hover:shadow-md">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[#00a63e]">
            <x-sb.icon name="package" class="h-5 w-5" />
        </div>
        <div class="flex-1">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <span class="font-black text-slate-900">
                    Pesanan <span class="text-[#00a63e]">#{{ $order->public_order_id }}</span>
                </span>
                <span class="text-xs font-semibold text-slate-400 mt-1 sm:mt-0">{{ $order->created_at->diffForHumans() }}</span>
            </div>
            <div class="mt-1 text-slate-600 font-medium font-sans">
                {{ $order->box_title }} &nbsp;•&nbsp; <span class="text-slate-400">{{ $order->restaurant_name }}</span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-extrabold text-[#00a63e] ring-1 ring-inset ring-emerald-600/20">
                    {{ $money((int) $order->amount_idr) }}
                </span>
                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">
                    {{ $paymentLabel($order->payment_method) }}
                </span>
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                    {{ $order->fulfillment_method === 'delivery' ? 'Delivery' : 'Pickup' }}
                </span>
                <span class="ml-auto text-xs font-medium text-slate-400 hidden sm:block">{{ $order->customer_email }}</span>
            </div>
        </div>
    </div>
@empty
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-12 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
            <x-sb.icon name="clock" class="h-6 w-6" />
        </div>
        <p class="mt-4 font-bold text-slate-800">Belum ada aktivitas</p>
        <p class="mt-1 text-xs text-slate-500">Pesan baru akan otomatis muncul di sini.</p>
    </div>
@endforelse
