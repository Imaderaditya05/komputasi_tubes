@forelse ($users as $u)
    @php
        $roleUi = match ($u->role) {
            'customer' => ['label' => 'Customer', 'class' => 'bg-emerald-100 text-emerald-800'],
            'seller', 'mitra' => ['label' => 'Seller', 'class' => 'bg-orange-100 text-orange-800'],
            'admin' => ['label' => 'Admin', 'class' => 'bg-violet-100 text-violet-800'],
            default => ['label' => $u->role, 'class' => 'bg-slate-100 text-slate-800'],
        };
        $orders = $orderCounts[$u->id] ?? 0;
    @endphp
    <tr class="border-b border-[#f3f4f6] last:border-0 hover:bg-slate-50/80">
        <td class="px-4 py-4 align-middle">
            <div class="font-black text-[#1e2939]">{{ $u->name }}</div>
            <div class="text-sm font-semibold text-[#6a7282]">{{ $u->email }}</div>
        </td>
        <td class="px-4 py-4 align-middle font-semibold text-[#4a5565]">{{ $u->phone ?? '—' }}</td>
        <td class="px-4 py-4 align-middle">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $roleUi['class'] }}">{{ $roleUi['label'] }}</span>
        </td>
        <td class="px-4 py-4 align-middle">
            @if ($u->is_active ?? true)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800"><x-sb.icon name="check-circle" class="h-3.5 w-3.5" /> Active</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700"><x-sb.icon name="x-circle" class="h-3.5 w-3.5" /> Inactive</span>
            @endif
        </td>
        <td class="px-4 py-4 align-middle text-sm font-bold text-[#1e2939]">{{ $u->created_at?->format('Y-m-d') }}</td>
        <td class="px-4 py-4 align-middle font-black text-[#2563eb]">{{ number_format($orders) }}</td>
        <td class="px-4 py-4 align-middle">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="edit-user rounded-lg bg-blue-100 p-2 text-blue-700 ring-1 ring-blue-200 hover:bg-blue-200"
                        title="Edit"
                        data-json="{{ e(json_encode([
                            'id' => $u->id,
                            'name' => $u->name,
                            'email' => $u->email,
                            'phone' => $u->phone,
                            'role' => $u->role,
                        ])) }}">
                    <x-sb.icon name="book-open" class="h-5 w-5" />
                </button>
                @if ($u->role !== 'admin')
                    <form method="post" action="{{ route('admin.users.toggle-active', $u) }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-100 p-2 text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-200" title="Toggle status">
                            <x-sb.icon name="users" class="h-5 w-5" />
                        </button>
                    </form>
                    <form method="post" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus pengguna ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-100 p-2 text-red-700 ring-1 ring-red-200 hover:bg-red-200" title="Delete">
                            <x-sb.icon name="x-mark" class="h-5 w-5" />
                        </button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-12 text-center text-base font-semibold text-[#6a7282]">Tidak ada pengguna.</td>
    </tr>
@endforelse
