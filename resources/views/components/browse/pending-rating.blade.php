@props([
    'publicOrderId',
])

<div {{ $attributes->class(['mt-3 rounded-2xl border border-amber-200 bg-amber-50/95 p-3 ring-1 ring-amber-100']) }}>
    <p class="text-xs font-black text-amber-950">Nilai pesanan & menu ini</p>
    <p class="mt-0.5 text-[11px] font-semibold leading-snug text-amber-900/85">
        Rating Anda memperbarui skor di katalog dan membantu mitra melihat umpan balik penjualan.
    </p>
    <form method="post" action="{{ route('orders.review', ['publicOrderId' => $publicOrderId]) }}" class="mt-2 space-y-2">
        @csrf
        <div>
            <label for="browse-pending-rating-{{ $publicOrderId }}" class="mb-1 block text-[10px] font-black uppercase tracking-wide text-amber-900/80">Bintang</label>
            <select
                name="rating"
                id="browse-pending-rating-{{ $publicOrderId }}"
                required
                class="w-full rounded-xl border-2 border-amber-200 bg-white px-3 py-2 text-sm font-bold text-[#1e2939] focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
            >
                <option value="" disabled selected>Pilih 1–5 ★</option>
                @for ($s = 5; $s >= 1; $s--)
                    <option value="{{ $s }}">{{ $s }} ★</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="browse-pending-comment-{{ $publicOrderId }}" class="mb-1 block text-[10px] font-black uppercase tracking-wide text-amber-900/80">Komentar (opsional)</label>
            <textarea
                name="comment"
                id="browse-pending-comment-{{ $publicOrderId }}"
                rows="2"
                maxlength="500"
                placeholder="Singkat saja…"
                class="w-full resize-none rounded-xl border-2 border-amber-200 bg-white px-3 py-2 text-xs font-semibold text-[#1e2939] placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
            ></textarea>
        </div>
        <button
            type="submit"
            class="w-full rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 py-2.5 text-xs font-black text-white shadow hover:opacity-95"
        >
            Kirim penilaian
        </button>
    </form>
</div>
