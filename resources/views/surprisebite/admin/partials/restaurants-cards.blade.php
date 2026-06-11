@forelse ($entries as $entry)
    @if ($entry['type'] === 'admin')
        @include('surprisebite.admin.partials.restaurants-card-admin', ['r' => $entry['admin'], 'money' => $money])
    @else
        @include('surprisebite.admin.partials.restaurants-card-mitra', ['r' => $entry['mitra'], 'money' => $money])
    @endif
@empty
    <div class="col-span-full flex flex-col items-center justify-center rounded-[24px] border-2 border-dashed border-slate-200 bg-white px-8 py-16 text-center shadow-sm">
        <p class="text-lg font-black text-slate-800">Belum ada mitra atau restoran katalog</p>
        <p class="mt-2 max-w-md text-sm font-semibold text-slate-500">Tambah dari katalog admin, atau tunggu mitra mendaftar.</p>
    </div>
@endforelse
