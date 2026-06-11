@php
    $img = $menu->image_url ?: $placeholderImg;
    $pct = $menu->savingsPercent();
    $menuJson = [
        'id' => $menu->id,
        'name' => $menu->name,
        'price' => (float) $menu->price,
        'original_price' => (float) $menu->original_price,
        'category' => $menu->category,
        'description' => $menu->description,
        'stock' => (int) $menu->stock,
        'pickup_time' => $menu->pickup_time,
        'image_url' => $menu->image_url,
        'savings_percent' => $pct,
    ];
@endphp
<div
    class="mystery-card group flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:border-emerald-200 hover:shadow-md"
    data-menu-id="{{ $menu->id }}"
    data-menu='@json($menuJson)'
>
    <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-100">
        <img src="{{ $img }}" alt="{{ $menu->name }}" class="h-full w-full object-cover" data-field="image">
        <span class="absolute right-3 top-3 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white shadow" data-field="stock-badge" data-stock="{{ $menu->stock }}">{{ $menu->stock }} Tersisa</span>
    </div>
    <div class="flex flex-1 flex-col p-4">
        <h3 class="text-lg font-black text-slate-900" data-field="name">{{ $menu->name }}</h3>
        <p class="mt-0.5 text-sm font-semibold text-emerald-600 @if(!$menu->category) hidden @endif" data-field="category">{{ $menu->category }}</p>
        <p class="mt-1 line-clamp-2 text-sm text-slate-600" data-field="subtitle">{{ $menu->description ?: ($menu->category ?? '') }}</p>

        <dl class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">Harga</dt>
                <dd class="font-bold text-emerald-600" data-field="price" data-raw="{{ $menu->price }}">{{ $rp($menu->price) }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">Nilai Asli</dt>
                <dd class="text-slate-400 line-through" data-field="original" data-raw="{{ $menu->original_price }}">{{ $rp($menu->original_price) }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">Hemat</dt>
                <dd class="font-bold text-orange-500" data-field="hemat">{{ $pct }}%</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-500">Waktu Pickup</dt>
                <dd class="font-semibold text-slate-700" data-field="pickup">{{ $menu->pickup_time ?: '—' }}</dd>
            </div>
            @if ((int) ($menu->ratings_count ?? 0) > 0 && $menu->avg_rating !== null)
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">Rating pembeli</dt>
                    <dd class="font-bold text-amber-600" data-field="buyer-rating">
                        ★ {{ number_format((float) $menu->avg_rating, 1) }}
                        <span class="text-xs font-semibold text-slate-500">({{ (int) $menu->ratings_count }} ulasan)</span>
                    </dd>
                </div>
            @endif
        </dl>

        <p class="mt-2 text-center text-xs font-medium text-slate-400 opacity-0 transition group-hover:opacity-100 sm:opacity-100">Klik di luar tombol untuk detail</p>

        <div class="mt-3 flex gap-2">
            <button type="button" class="btn-edit-mystery inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#00a63e] to-[#00bc7d] py-2.5 text-sm font-bold text-white shadow">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Edit
            </button>
            <button type="button" class="btn-delete-mystery inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-red-600 hover:bg-red-100" title="Hapus">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
        </div>
    </div>
</div>
