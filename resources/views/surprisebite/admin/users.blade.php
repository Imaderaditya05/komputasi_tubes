<x-layouts.admin title="User Management" active="users">
    @php
        $pathAdminUsers = parse_url(route('admin.users'), PHP_URL_PATH) ?: '/admin/users';
        $pathLiveUsers = parse_url(route('admin.api.live.users'), PHP_URL_PATH) ?: '/admin/api/live/users';
    @endphp
    <div id="admin-users-config"
         class="hidden"
         data-store-url="{{ $pathAdminUsers }}"
         data-users-base="{{ $pathAdminUsers }}"
         data-live-url="{{ $pathLiveUsers }}"></div>

    <div class="rounded-[24px] border-2 border-[#f3f4f6] bg-white p-6 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] sm:p-8"
         style="background-image: linear-gradient(141.254deg, rgb(249, 250, 251) 0%, rgba(219, 234, 254, 0.35) 100%);">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-base font-bold text-[#4a5565] hover:text-[#2563eb]">
            <span class="text-lg" aria-hidden="true">←</span>
            Back to Admin Dashboard
        </a>

        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-[#1e2939] sm:text-4xl">User Management</h2>
                <p class="mt-1 text-base font-semibold text-[#4a5565]">Panel pengelolaan SurpriseBite</p>
            </div>
        </div>

        @php
            $activeListFilter = $activeListFilter ?? false;
            $userStatHref = function (?string $role, bool $activeOnly) use ($q) {
                $query = array_filter([
                    'q' => $q !== '' ? $q : null,
                    'role' => $role !== null && $role !== '' ? $role : null,
                    'active' => $activeOnly ? '1' : null,
                ], fn ($v) => $v !== null && $v !== '');

                return route('admin.users', $query).'#rt-users-table';
            };
            $userStatRing = function (string $key) use ($roleFilter, $activeListFilter): string {
                $on = match ($key) {
                    'all' => $roleFilter === null && ! $activeListFilter,
                    'customers' => $roleFilter === 'customer' && ! $activeListFilter,
                    'sellers' => $roleFilter === 'seller' && ! $activeListFilter,
                    'active' => $activeListFilter,
                    default => false,
                };

                return $on
                    ? 'ring-2 ring-offset-2 ring-[#2563eb] shadow-lg'
                    : 'ring-2 ring-transparent hover:ring-slate-200';
            };
        @endphp

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" id="rt-user-stat-cards" role="navigation" aria-label="Ringkasan dan filter pengguna">
            <a href="{{ $userStatHref(null, false) }}"
               class="block rounded-2xl border-2 border-slate-200 bg-white px-6 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#2563eb] focus-visible:ring-offset-2 {{ $userStatRing('all') }}"
               @if ($roleFilter === null && ! $activeListFilter) aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Total Users <span class="block text-[10px] font-semibold normal-case text-slate-400">Semua pengguna</span></p>
                <p class="mt-2 text-4xl font-black text-slate-900" id="rt-user-total">{{ number_format($stats['total']) }}</p>
            </a>
            <a href="{{ $userStatHref('customer', false) }}"
               class="block rounded-2xl border-2 border-emerald-100 bg-white px-6 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#00a63e] focus-visible:ring-offset-2 {{ $userStatRing('customers') }}"
               @if ($roleFilter === 'customer' && ! $activeListFilter) aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Customers</p>
                <p class="mt-2 text-4xl font-black text-[#00a63e]" id="rt-user-customers">{{ number_format($stats['customers']) }}</p>
            </a>
            <a href="{{ $userStatHref('seller', false) }}"
               class="block rounded-2xl border-2 border-orange-100 bg-white px-6 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#f97316] focus-visible:ring-offset-2 {{ $userStatRing('sellers') }}"
               @if ($roleFilter === 'seller' && ! $activeListFilter) aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Sellers</p>
                <p class="mt-2 text-4xl font-black text-[#f97316]" id="rt-user-sellers">{{ number_format($stats['sellers']) }}</p>
            </a>
            <a href="{{ $userStatHref(null, true) }}"
               class="block rounded-2xl border-2 border-sky-100 bg-white px-6 py-5 shadow-md outline-none transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#0284c7] focus-visible:ring-offset-2 {{ $userStatRing('active') }}"
               @if ($activeListFilter) aria-current="page" @endif>
                <p class="text-sm font-bold text-[#4a5565]">Active</p>
                <p class="mt-2 text-4xl font-black text-[#0284c7]" id="rt-user-active">{{ number_format($stats['active']) }}</p>
            </a>
        </div>

        <div id="rt-pending-mitra-banner"
             class="{{ ($stats['pending_mitra'] ?? 0) > 0 ? '' : 'hidden' }} mt-5 rounded-2xl border-2 border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-950 ring-1 ring-amber-100/80 shadow-sm">
            <span id="rt-pending-mitra-count">{{ number_format(max(0, (int) ($stats['pending_mitra'] ?? 0))) }}</span>
            pendaftar mitra menunggu persetujuan Anda.
            Cek kolom Actions pada tabel di bawah — gunakan tombol Setujui / Tolak.
        </div>

        <div class="mt-8 rounded-[20px] border-2 border-[#e5e7eb] bg-white/95 p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:gap-5">
                <form method="get" action="{{ route('admin.users') }}" id="form-users-filter" class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:gap-3">
                    @if ($activeListFilter)
                        <input type="hidden" name="active" value="1" />
                    @endif
                    <label class="relative block min-w-0 flex-1">
                        <span class="sr-only">Cari nama atau email</span>
                        <span class="pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-slate-400"><x-sb.icon name="search" class="h-5 w-5" /></span>
                        <input type="search" name="q" value="{{ $q }}"
                               placeholder="Search by name or email…"
                               enterkeyhint="search"
                               autocomplete="off"
                               class="block w-full rounded-[14px] border-2 border-[#e5e7eb] bg-white py-3.5 pl-11 pr-4 text-base font-semibold text-[#1e2939] placeholder:text-[#71717a]/70 shadow-[inset_0_1px_0_rgba(255,255,255,0.9)] focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25 [&::-webkit-search-cancel-button]:opacity-60" />
                    </label>
                    <div class="w-full shrink-0 sm:w-52">
                        <label class="sr-only" for="users-role-filter">Filter role</label>
                        <select id="users-role-filter" name="role" data-users-role-filter
                                class="w-full rounded-[14px] border-2 border-[#e5e7eb] bg-white px-3.5 py-3.5 text-sm font-bold text-[#364153] shadow-[inset_0_1px_0_rgba(255,255,255,0.9)] focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25">
                            <option value="" @selected($roleFilter === null)>Semua role</option>
                            <option value="admin" @selected($roleFilter === 'admin')>Admin</option>
                            <option value="seller" @selected($roleFilter === 'seller')>Seller</option>
                            <option value="customer" @selected($roleFilter === 'customer')>Customer</option>
                        </select>
                    </div>
                </form>
                <button type="button" id="btn-open-add-user" class="inline-flex h-12 min-h-[2.875rem] w-full shrink-0 items-center justify-center gap-2 rounded-[14px] bg-[#2563eb] px-5 text-sm font-black text-white shadow-md hover:bg-[#1d4ed8] lg:h-12 lg:w-auto">
                    + Add User
                </button>
            </div>
        </div>

        <div id="rt-users-table" class="mt-8 overflow-hidden rounded-[24px] border-2 border-[#f3f4f6] bg-white shadow-lg scroll-mt-24">
            <div class="overflow-x-auto">
                <table class="min-w-[1000px] w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#2563eb] to-[#1d4ed8] text-white">
                            <th class="px-4 py-4 text-sm font-black">User</th>
                            <th class="px-4 py-4 text-sm font-black">Contact</th>
                            <th class="px-4 py-4 text-sm font-black">Role</th>
                            <th class="px-4 py-4 text-sm font-black">Status</th>
                            <th class="px-4 py-4 text-sm font-black">Join Date</th>
                            <th class="px-4 py-4 text-sm font-black">Orders</th>
                            <th class="px-4 py-4 text-sm font-black">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rt-users-tbody">
                        @include('surprisebite.admin.partials.users-tbody', [
                            'users' => $users,
                            'orderCounts' => $orderCounts,
                            'authUserId' => auth()->id(),
                        ])
                    </tbody>
                </table>
            </div>
        </div>

        <div id="rt-users-pagination" class="mt-6 flex min-h-[2.5rem] justify-center">
            @include('surprisebite.admin.partials.users-pagination-fragment', ['users' => $users])
        </div>
    </div>

    <dialog id="dlg-user-add" class="fixed left-1/2 top-1/2 z-50 w-[calc(100vw-1.5rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-[1.25rem] border-2 border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-900/45 backdrop:backdrop-blur-[1px]">
        <form id="form-user-add" method="post" action="{{ route('admin.users.store') }}" class="flex max-h-[90vh] flex-col">
            @csrf
            <div class="overflow-y-auto p-6 pb-4">
                <h3 class="text-xl font-black text-slate-900">Tambah User Baru</h3>
                <div class="mt-4 space-y-4">
                    <label class="block text-sm font-bold text-slate-700">Name
                        <input name="name" type="text" required autocomplete="name"
                               class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Email
                        <input name="email" type="email" required autocomplete="email"
                               class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Phone
                        <input name="phone" type="text" autocomplete="tel"
                               class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Password
                        <input name="password" type="password" required autocomplete="new-password"
                               class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Confirm Password
                        <input name="password_confirmation" type="password" required autocomplete="new-password"
                               class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Role
                        <select name="role" required
                                class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25">
                            <option value="" selected>Pilih role</option>
                            <option value="customer">Customer</option>
                            <option value="seller">Seller</option>
                            <option value="mitra">Mitra</option>
                        </select>
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Status
                        <select name="is_active" required
                                class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="flex shrink-0 flex-col gap-3 border-t border-slate-100 p-4 sm:flex-row sm:justify-end">
                <button type="button" data-close-add-user class="rounded-xl bg-slate-200 px-5 py-3 text-sm font-black text-slate-800 hover:bg-slate-300">Cancel</button>
                <button type="submit" class="rounded-xl bg-[#2563eb] px-5 py-3 text-sm font-black text-white hover:bg-blue-700">Save</button>
            </div>
        </form>
    </dialog>

    <dialog id="dlg-user-edit" class="fixed left-1/2 top-1/2 z-50 w-[calc(100vw-1.5rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-[1.25rem] border-2 border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-900/45 backdrop:backdrop-blur-[1px]">
        <form id="form-user-edit" method="post" class="flex max-h-[90vh] flex-col">
            @csrf
            @method('PUT')
            <div class="overflow-y-auto p-6 pb-4">
                <h3 class="text-xl font-black text-slate-900">Edit User</h3>
                <div class="mt-4 space-y-4">
                    <label class="block text-sm font-bold text-slate-700">Name
                        <input name="name" id="u-edit-name" required type="text"
                               class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Email
                        <input name="email" id="u-edit-email" type="email" required
                               class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Phone
                        <input name="phone" id="u-edit-phone" type="text"
                               class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
                    </label>
                    <div id="u-edit-role-wrap">
                        <label class="block text-sm font-bold text-slate-700">Role
                            <select name="role" id="u-edit-role"
                                    class="mt-1.5 w-full rounded-xl border-2 border-slate-200 px-3 py-3 text-base font-semibold focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25">
                                <option value="customer">Customer</option>
                                <option value="seller">Seller</option>
                                <option value="mitra">Mitra</option>
                            </select>
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex shrink-0 flex-col gap-3 border-t border-slate-100 p-4 sm:flex-row sm:justify-end">
                <button type="button" data-close-edit-user class="rounded-xl bg-slate-200 px-5 py-3 text-sm font-black text-slate-800 hover:bg-slate-300">Cancel</button>
                <button type="submit" class="rounded-xl bg-[#2563eb] px-5 py-3 text-sm font-black text-white hover:bg-blue-700">Save</button>
            </div>
        </form>
    </dialog>

    <dialog id="dlg-user-delete" class="fixed left-1/2 top-1/2 z-[55] w-[calc(100vw-1.5rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-[1.25rem] border-2 border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-900/45 backdrop:backdrop-blur-[1px]">
        <div class="p-6 pb-4">
            <h3 class="text-xl font-black text-slate-900">Hapus User</h3>
            <p class="mt-4 text-base font-semibold leading-relaxed text-[#4a5565]">
                Apakah Anda yakin ingin menghapus user <span id="dlg-delete-user-name" class="font-black text-[#1e2939]"></span>? Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
        <div class="flex shrink-0 flex-row flex-wrap justify-end gap-3 border-t border-slate-100 p-4">
            <button type="button" data-close-delete-user class="rounded-xl bg-slate-200 px-5 py-3 text-sm font-black text-slate-800 hover:bg-slate-300">
                Batal
            </button>
            <button type="button" id="btn-confirm-delete-user" class="rounded-xl bg-red-600 px-5 py-3 text-sm font-black text-white shadow-md hover:bg-red-700">
                Hapus
            </button>
        </div>
    </dialog>

    <div id="admin-users-toast" class="fixed bottom-6 left-1/2 z-[60] flex w-[min(100vw-2rem,24rem)] -translate-x-1/2 flex-col gap-2 pointer-events-none"></div>
</x-layouts.admin>
