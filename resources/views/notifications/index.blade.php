<x-layouts.app :title="'Notifikasi Saya • SurpriseBite'" variant="marketing">
@php($trackUrlTpl = route('orders.track', ['publicOrderId' => '__ORDER__']))
<div
    class="pb-16 pt-6 sm:pt-8"
    data-customer-notifications-page
    data-notifications-url="{{ route('api.live.notifications') }}"
    data-patch-read-prefix="{{ url('/notifications') }}"
    data-initial-track-tpl="{{ e($trackUrlTpl) }}"
>
    <div class="mx-auto max-w-3xl px-2 sm:px-0">
        @if (session('status'))
            <div class="mb-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-100" role="status">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="flex items-center gap-3 text-3xl font-black text-[#1e2939] sm:text-4xl">
                    <span class="text-[#ff6900]" aria-hidden="true">🔔</span>
                    Notifikasi Saya
                </h1>
                <p class="mt-2 text-base italic text-[#6a7282] sm:text-lg">Pantau pembaruan pesanan dan status transaksi Anda</p>
            </div>
            <form method="post" action="{{ route('notifications.mark-all-read') }}" class="shrink-0">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-full border-2 border-[#00a63e] bg-white px-5 py-2.5 text-sm font-black text-[#00a63e] shadow-sm transition hover:bg-[#f0fdf4]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Tandai Semua Terbaca
                </button>
            </form>
        </div>

        <div id="customer-notifications-root" class="space-y-4">
            @forelse ($notifications as $n)
                <article
                    class="rounded-3xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6"
                    data-notification-id="{{ $n->id }}"
                    data-notification-read="{{ $n->read_at ? '1' : '0' }}"
                >
                    <div class="flex gap-4">
                        @php($isUnreadCard = ! $n->read_at)
                        <div
                            class="{{ $isUnreadCard ? 'bg-[#bbf7d0] text-[#14532d]' : 'bg-slate-100 text-[#475569]' }} flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-2 ring-black/5"
                            aria-hidden="true"
                        >
                            @if (str_contains((string) $n->type, 'payment'))
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            @elseif (str_contains((string) $n->type, 'order_created'))
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            @else
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H6a2 2 0 00-2 2v18z" /></svg>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-start justify-between gap-2">
                                <h2 class="{{ $isUnreadCard ? 'text-[#0f172a]' : 'text-slate-700' }} text-lg font-black">{{ $n->title }}</h2>
                                <time
                                    datetime="{{ $n->created_at?->toIso8601String() }}"
                                    class="notif-relative-time shrink-0 text-xs font-semibold uppercase tracking-wide text-[#64748b]"
                                    data-created-at="{{ $n->created_at?->toIso8601String() }}"
                                >
                                    {{ $n->created_at?->timezone(config('app.timezone'))->diffForHumans() ?? '' }}
                                </time>
                            </div>
                            <p class="{{ $isUnreadCard ? 'text-[#475569]' : 'text-[#64748b]' }} text-sm leading-relaxed">{{ $n->body }}</p>
                            @if ($n->public_order_id)
                                <p class="mt-2 font-mono text-xs font-semibold text-[#00a63e]">{{ $n->public_order_id }}</p>
                            @endif
                            <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-bold">
                                @if ($n->public_order_id)
                                    <a href="{{ route('orders.track', ['publicOrderId' => $n->public_order_id]) }}" class="text-[#00a63e] hover:underline">Lacak pesanan</a>
                                @endif
                                @if (! $n->read_at)
                                    <button
                                        type="button"
                                        class="border-0 bg-transparent p-0 text-[#6366f1] hover:underline js-notif-mark-read"
                                        data-notification-id="{{ $n->id }}"
                                    >
                                        Tandai dibaca
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl bg-white px-8 py-16 text-center shadow-md ring-1 ring-slate-100">
                    <div class="mb-4 text-6xl" aria-hidden="true">🔔</div>
                    <h2 class="mb-3 text-2xl font-black text-[#1e2939]">Belum ada notifikasi</h2>
                    <p class="mb-8 text-[#6a7282]">Pembayaran, status pesanan, dan pembaruan penting akan muncul otomatis di sini.</p>
                    <a href="{{ route('browse') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#ff6900] to-[#f54900] px-8 py-3 text-base font-bold text-white shadow-lg transition hover:shadow-xl">
                        Jelajahi Mystery Boxes
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
</x-layouts.app>
