@forelse ($restaurants as $r)
    @php
        $boxes = is_array($r->boxes_json) ? $r->boxes_json : [];
        $firstBox = $boxes[0] ?? null;
    @endphp
    <div class="flex flex-col overflow-hidden rounded-3xl border-2 border-[#f3f4f6] bg-white shadow-lg">
        <div class="relative aspect-[16/10] w-full overflow-hidden bg-slate-100">
            <img src="{{ $r->image_url ?: asset('images/logo.png') }}" alt="" class="h-full w-full object-cover" loading="lazy" />
            <span class="absolute right-3 top-3 rounded-full px-3 py-1 text-xs font-black shadow {{ $r->status === 'active' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }}">
                {{ $r->status === 'active' ? '✓ Active' : 'Pending' }}
            </span>
        </div>
        <div class="flex flex-1 flex-col p-5">
            <h3 class="text-lg font-black text-[#1e2939]">{{ $r->name }}</h3>
            <p class="mt-1 flex items-center gap-1 text-sm font-semibold text-[#6a7282]">
                <x-sb.icon name="map-pin" class="h-4 w-4 shrink-0" /> {{ $r->area ?: '—' }}
            </p>
            <p class="mt-2 text-sm font-bold text-amber-600">★ {{ number_format((float) $r->rating, 1) }} <span class="font-semibold text-slate-500">({{ $r->reviews_count }} reviews)</span></p>
            <p class="mt-2 line-clamp-2 text-sm text-[#4a5565]">{{ \Illuminate\Support\Str::limit($r->description ?? '', 120) }}</p>
            <p class="mt-2 text-xs font-bold text-slate-500">{{ count($boxes) }} Mystery Box(es)</p>
            @if ($firstBox)
                <div class="mt-3 rounded-xl bg-orange-50 px-3 py-2 text-sm">
                    <div class="font-black text-[#1e2939]">{{ $firstBox['title'] ?? 'Box' }}</div>
                    <div class="font-black text-[#f97316]">{{ $money((int) ($firstBox['price'] ?? 0)) }}</div>
                </div>
            @else
                <div class="mt-3 rounded-xl border border-dashed border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500">Belum ada mystery box</div>
            @endif
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('home', ['q' => $r->name]) }}"
                   class="inline-flex flex-1 items-center justify-center gap-1 rounded-xl border-2 border-[#f97316] px-3 py-2 text-sm font-black text-[#f97316] hover:bg-orange-50">View</a>
                <button type="button"
                        class="edit-btn inline-flex flex-1 items-center justify-center gap-1 rounded-xl bg-gradient-to-r from-[#f97316] to-[#ea580c] px-3 py-2 text-sm font-black text-white"
                        data-json="{{ e(json_encode([
                            'id' => $r->id,
                            'name' => $r->name,
                            'location' => $r->area,
                            'image_url' => $r->image_url,
                            'description' => $r->description,
                            'rating' => (float) $r->rating,
                            'reviews' => (int) $r->reviews_count,
                            'status' => $r->status,
                            'box_title' => $firstBox['title'] ?? '',
                            'box_price' => isset($firstBox['price']) ? (int) $firstBox['price'] : '',
                        ])) }}">Edit</button>
                <form method="post" action="{{ route('admin.restaurants.destroy', $r) }}" class="inline"
                      onsubmit="return confirm('Hapus restoran ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border-2 border-red-200 text-red-600 hover:bg-red-50" title="Delete">
                        <x-sb.icon name="x-mark" class="h-5 w-5" />
                    </button>
                </form>
            </div>
        </div>
    </div>
@empty
    <p class="col-span-full mt-4 text-center text-base font-semibold text-[#6a7282]">Tidak ada restoran yang cocok.</p>
@endforelse
