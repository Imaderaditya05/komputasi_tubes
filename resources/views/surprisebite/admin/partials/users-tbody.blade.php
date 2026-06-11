@php
    $authId = isset($authUserId) ? (int) $authUserId : null;
@endphp
@forelse ($users as $u)
    @php
        $roleUi = match ($u->role) {
            'customer' => ['label' => 'Customer', 'class' => 'bg-emerald-100 text-emerald-800'],
            'seller', 'mitra' => ['label' => 'Seller', 'class' => 'bg-orange-100 text-orange-800'],
            'admin' => ['label' => 'Admin', 'class' => 'bg-violet-100 text-violet-800'],
            default => ['label' => $u->role, 'class' => 'bg-slate-100 text-slate-800'],
        };
        $orders = $orderCounts[$u->id] ?? 0;
        $phoneDisp = trim((string) ($u->phone ?? ''));
        $canEdit = $u->role !== 'admin' || (int) $u->id === $authId;
        $canDelete = $u->role !== 'admin' && (int) $u->id !== $authId;
    @endphp
    <tr class="border-b border-[#f3f4f6] last:border-0 hover:bg-slate-50/80">
        <td class="px-4 py-4 align-middle">
            <div class="font-black text-[#1e2939]">{{ $u->name }}</div>
            <div class="text-sm font-semibold text-[#6a7282]">{{ $u->email }}</div>
        </td>
        <td class="px-4 py-4 align-middle font-semibold text-[#4a5565]">{{ $phoneDisp !== '' ? $phoneDisp : '—' }}</td>
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
            <div class="flex max-w-[14rem] flex-col gap-2">
                <div class="flex flex-wrap items-center gap-2">
                @if ($canEdit)
                    <button type="button" class="edit-user inline-flex items-center justify-center rounded-lg bg-blue-100 p-2 text-blue-700 ring-1 ring-blue-200 hover:bg-blue-200"
                            title="Edit"
                            data-user-id="{{ $u->id }}"
                            data-user-name="{{ e($u->name) }}"
                            data-user-email="{{ e($u->email) }}"
                            data-user-phone="{{ e($u->phone ?? '') }}"
                            data-user-role="{{ e($u->role) }}">
                        <x-sb.icon name="pencil-square" class="h-5 w-5 shrink-0 pointer-events-none" />
                    </button>
                @endif
                @if ($canDelete)
                    <button type="button" class="delete-user inline-flex items-center justify-center rounded-lg bg-red-100 p-2 text-red-700 ring-1 ring-red-200 hover:bg-red-200"
                            title="Delete"
                            data-user-id="{{ $u->id }}"
                            data-label="{{ e($u->name) }}">
                        <x-sb.icon name="trash" class="h-5 w-5 shrink-0 pointer-events-none" />
                    </button>
                @endif
                </div>

                @if ($u->role === 'mitra' && \Illuminate\Support\Facades\Schema::hasColumn('users', 'mitra_approval_status'))
                    @php
                        $mas = (string) ($u->mitra_approval_status ?? 'approved');
                        $mitraLabel = match ($mas) {
                            'pending' => ['text' => 'Mitra pending', 'cls' => 'bg-amber-100 text-amber-900 ring-amber-200'],
                            'rejected' => ['text' => 'Mitra ditolak', 'cls' => 'bg-red-100 text-red-800 ring-red-200'],
                            default => ['text' => 'Mitra disetujui', 'cls' => 'bg-emerald-100 text-emerald-900 ring-emerald-200'],
                        };
                    @endphp
                    <div class="flex w-full min-w-[10rem] flex-col gap-1.5 rounded-xl border border-slate-100 bg-slate-50 px-2 py-2 ring-1 ring-slate-100">
                        <span class="inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wide ring-1 {{ $mitraLabel['cls'] }}">{{ $mitraLabel['text'] }}</span>
                        @if ($mas === 'pending')
                            <form method="post" action="{{ route('admin.users.mitra-approval', $u) }}" class="block w-full">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="mitra_approval_status" value="approved" />
                                <button type="submit" class="w-full rounded-lg bg-emerald-600 px-2 py-1.5 text-[11px] font-black uppercase tracking-wide text-white hover:bg-emerald-700">
                                    Setujui mitra
                                </button>
                            </form>
                            <form method="post" action="{{ route('admin.users.mitra-approval', $u) }}" class="block w-full">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="mitra_approval_status" value="rejected" />
                                <button type="submit" class="w-full rounded-lg bg-white px-2 py-1.5 text-[11px] font-black uppercase tracking-wide text-red-700 ring-1 ring-red-200 hover:bg-red-50">
                                    Tolak
                                </button>
                            </form>
                        @elseif ($mas === 'rejected')
                            <form method="post" action="{{ route('admin.users.mitra-approval', $u) }}" class="block w-full">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="mitra_approval_status" value="approved" />
                                <button type="submit" class="w-full rounded-lg bg-emerald-600 px-2 py-1.5 text-[11px] font-black uppercase tracking-wide text-white hover:bg-emerald-700">
                                    Setujui (batalkan tolak)
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-4 py-14 align-middle">
            <div class="mx-auto flex max-w-md flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/80 px-6 py-10 text-center">
                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-200/80 text-slate-500">
                    <x-sb.icon name="users" class="h-8 w-8" />
                </div>
                <p class="text-base font-black text-[#1e2939]">Tidak ada pengguna</p>
                <p class="mt-2 text-sm font-semibold text-[#6a7282]">Coba ubah kata kunci pencarian atau filter role.</p>
            </div>
        </td>
    </tr>
@endforelse
