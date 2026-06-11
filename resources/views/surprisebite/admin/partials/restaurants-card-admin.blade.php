@php
    $boxes = is_array($r->boxes_json) ? $r->boxes_json : [];
    $firstBox = $boxes[0] ?? null;
    $badge = $r->mitraAccessBadge();
    $displayAddr = trim((string) ($r->address_line ?: $r->area ?: $r->city ?: ''));
    if ($displayAddr === '') {
        $displayAddr = '—';
    }
    $ownerLabel = filled($r->owner_name) ? $r->owner_name : '—';
    $desc = trim((string) ($r->description ?? ''));
    $cardKey = 'admin-'.$r->id;
    $patchBase = url('/admin/restaurants');
    $editPayload = [
        'id' => $r->id,
        'name' => $r->name,
        'owner_name' => $r->owner_name,
        'location' => $r->area,
        'address_line' => $r->address_line,
        'image_url' => $r->image_url,
        'description' => $r->description,
        'rating' => (float) $r->rating,
        'reviews' => (int) $r->reviews_count,
        'status' => $r->status,
        'box_title' => $firstBox['title'] ?? '',
        'box_price' => isset($firstBox['price']) ? (int) $firstBox['price'] : '',
    ];
@endphp
<article
    class="admin-mitra-card flex flex-col overflow-hidden rounded-[24px] border-2 border-[#f3f4f6] bg-white shadow-[0_10px_40px_-10px_rgba(0,0,0,0.12)]"
    data-admin-mitra-card
    data-card-key="{{ $cardKey }}"
    data-restaurant-id="{{ $r->id }}"
    data-source="admin"
    data-mitra-status="{{ $r->status }}"
>
    <p class="border-b border-slate-100 bg-slate-50 px-4 py-2 text-center text-[10px] font-black uppercase tracking-wider text-slate-500">Katalog admin</p>
    <div class="relative aspect-[16/10] w-full shrink-0 overflow-hidden bg-slate-100">
        <img src="{{ $r->image_url ?: asset('images/logo.png') }}" alt="" class="h-full w-full object-cover" loading="lazy" />
        <span
            data-mitra-badge
            class="mitra-status-badge absolute right-3 top-3 rounded-full px-3 py-1 text-xs font-black shadow {{ $badge['classes'] }}"
        >{{ $badge['label'] }}</span>
    </div>
    <div class="flex min-h-0 flex-1 flex-col p-5">
        <h3 class="text-lg font-black tracking-tight text-[#1e2939]">{{ $r->name }}</h3>
        <p class="mt-2 flex items-start gap-1.5 text-sm font-semibold text-[#6a7282]">
            <x-sb.icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0" />
            <span class="leading-snug break-words">{{ $displayAddr }}</span>
        </p>
        <p class="mt-2 text-sm font-semibold text-slate-700">Owner: <span class="font-bold text-[#1e2939]">{{ $ownerLabel }}</span></p>
        @if ($desc !== '')
            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-[#4a5565]">{{ \Illuminate\Support\Str::limit($desc, 180) }}</p>
        @endif
        <p class="mt-3 text-sm font-bold text-slate-600">Total Mystery Boxes: <span class="text-[#1e2939]">{{ count($boxes) }}</span></p>

        <div class="mt-auto flex flex-wrap items-stretch gap-2 pt-4">
            <button
                type="button"
                class="btn-validasi-mitra inline-flex min-h-[44px] flex-1 items-center justify-center rounded-xl bg-gradient-to-r from-[#f97316] to-[#ea580c] px-4 py-2.5 text-sm font-black text-white shadow-md shadow-orange-500/20 transition hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-60"
                data-card-key="{{ $cardKey }}"
                data-patch-base="{{ $patchBase }}"
                data-restaurant-id="{{ $r->id }}"
                data-restaurant-name="{{ e($r->name) }}"
                data-current-status="{{ $r->status }}"
            >Validasi</button>
            <button
                type="button"
                class="btn-reject-mitra inline-flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-xl border-2 border-red-200 bg-white text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-60"
                title="Tolak / kunci akses"
                data-card-key="{{ $cardKey }}"
                data-patch-base="{{ $patchBase }}"
                data-restaurant-id="{{ $r->id }}"
                data-restaurant-name="{{ e($r->name) }}"
            ><x-sb.icon name="x-mark" class="h-5 w-5" /></button>
            <button
                type="button"
                class="edit-btn mt-1 w-full rounded-lg py-2 text-center text-xs font-bold text-slate-500 underline decoration-slate-300 underline-offset-2 hover:text-[#f97316]"
                data-json="{{ e(json_encode($editPayload)) }}"
            >Edit detail</button>
        </div>
    </div>
</article>
