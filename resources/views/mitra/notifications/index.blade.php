@php($r = auth()->user() ? \App\Models\Restaurant::where('user_id', auth()->id())->orderBy('id')->first() : null)
<x-layouts.app title="Notifikasi Mitra — SurpriseBite" variant="marketing">
<div class="pb-16 pt-6 sm:pt-8">
    <div class="mx-auto max-w-3xl px-2 sm:px-0">
        <span class="hidden" aria-hidden="true" data-patch-read-prefix="{{ url('/mitra/notifications') }}"></span>

        @if (session('status'))
            <div class="mb-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100" role="status">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="flex items-center gap-3 text-3xl font-black text-[#1e2939] sm:text-4xl">
                    <span class="text-[#ff6900]" aria-hidden="true">🔔</span>
                    Notifikasi Mitra
                </h1>
                <p class="mt-2 text-base italic text-[#6a7282] sm:text-lg">Pesanan checkout, pembayaran, dan status selesai untuk restoran Anda</p>
            </div>
            <form method="post" action="{{ route('mitra.notifications.mark-all-read') }}" class="shrink-0">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-full border-2 border-[#00a63e] bg-white px-5 py-2.5 text-sm font-black text-[#00a63e] shadow-sm transition hover:bg-[#f0fdf4]">
                    Tandai Semua Terbaca
                </button>
            </form>
        </div>

        <div class="space-y-4">
            @forelse ($notifications as $n)
                <article class="rounded-3xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6">
                    <div class="flex gap-4">
                        @php($isUnreadCard = ! $n->read_at)
                        <div class="{{ $isUnreadCard ? 'bg-[#bbf7d0] text-[#14532d]' : 'bg-slate-100 text-[#475569]' }} flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-2 ring-black/5" aria-hidden="true">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0h6z" /></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <h2 class="text-lg font-black text-[#1e2939]">{{ $n->title }}</h2>
                                @if ($n->public_order_id)
                                    <span class="shrink-0 rounded-full bg-slate-900/5 px-3 py-0.5 text-xs font-black text-[#475569]">{{ $n->public_order_id }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-[#4a5565]">{{ $n->body }}</p>
                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                @if ($isUnreadCard)
                                    <button
                                        type="button"
                                        class="js-notif-mark-read rounded-full bg-[#00a63e] px-4 py-2 text-xs font-black text-white shadow-sm hover:bg-[#008f36]"
                                        data-notification-id="{{ $n->id }}"
                                    >
                                        Tandai dibaca
                                    </button>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">Sudah dibaca</span>
                                @endif
                                @if ($r !== null && $n->public_order_id)
                                    <a href="{{ route('restaurants.orders.show', [$r, $n->public_order_id]) }}" class="text-xs font-bold text-[#00a63e] hover:underline">Lihat pesanan</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl bg-white px-8 py-16 text-center shadow-md ring-1 ring-slate-100">
                    <div class="mb-4 text-6xl" aria-hidden="true">🔔</div>
                    <h2 class="mb-3 text-2xl font-black text-[#1e2939]">Belum ada notifikasi</h2>
                    <p class="mb-8 text-[#6a7282]">Kelola pesanan checkout di halaman «Pesanan masuk», atau pantau pembayaran &amp; status di sini.</p>
                    <a href="{{ $r !== null ? route('restaurants.orders.index', $r) : route('mitra.dashboard') }}" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-8 py-3 text-base font-bold text-white shadow-lg hover:opacity-95">Ke Pesanan Checkout</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
</x-layouts.app>
