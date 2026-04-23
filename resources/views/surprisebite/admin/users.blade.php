<x-layouts.admin title="User Management" active="users">
    <div class="rounded-[24px] border-2 border-[#f3f4f6] bg-white p-6 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] sm:p-8"
         style="background-image: linear-gradient(141.254deg, rgb(249, 250, 251) 0%, rgba(219, 234, 254, 0.35) 100%);">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-base font-bold text-[#4a5565] hover:text-[#2563eb]">
            <span class="text-lg" aria-hidden="true">←</span>
            Back to Admin Dashboard
        </a>

        <div class="mt-4">
            <h2 class="text-3xl font-black tracking-tight text-[#1e2939] sm:text-4xl">User Management</h2>
            <p class="mt-1 text-base font-semibold text-[#4a5565]">Kelola Customer &amp; Seller</p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border-2 border-slate-200 bg-white px-6 py-5 shadow-md">
                <p class="text-sm font-bold text-[#4a5565]">Total Users</p>
                <p class="mt-2 text-4xl font-black text-slate-900" id="rt-user-total">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-2xl border-2 border-emerald-100 bg-white px-6 py-5 shadow-md">
                <p class="text-sm font-bold text-[#4a5565]">Customers</p>
                <p class="mt-2 text-4xl font-black text-[#00a63e]" id="rt-user-customers">{{ number_format($stats['customers']) }}</p>
            </div>
            <div class="rounded-2xl border-2 border-orange-100 bg-white px-6 py-5 shadow-md">
                <p class="text-sm font-bold text-[#4a5565]">Sellers</p>
                <p class="mt-2 text-4xl font-black text-[#f97316]" id="rt-user-sellers">{{ number_format($stats['sellers']) }}</p>
            </div>
            <div class="rounded-2xl border-2 border-sky-100 bg-white px-6 py-5 shadow-md">
                <p class="text-sm font-bold text-[#4a5565]">Active</p>
                <p class="mt-2 text-4xl font-black text-[#0284c7]" id="rt-user-active">{{ number_format($stats['active']) }}</p>
            </div>
        </div>

        <form method="get" action="{{ route('admin.users') }}" class="mt-8 flex flex-col gap-4 lg:flex-row lg:items-center">
            <div class="relative min-w-0 flex-1">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-sb.icon name="search" class="h-5 w-5" /></span>
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Search by name or email..."
                       class="w-full rounded-[14px] border-2 border-[#e5e7eb] py-3 pl-12 pr-4 text-base font-semibold text-[#1e2939] placeholder:text-[#71717a]/70 focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25" />
            </div>
            <select name="role" onchange="this.form.submit()"
                    class="min-w-[160px] rounded-[14px] border-2 border-[#e5e7eb] bg-white px-4 py-3 text-sm font-bold text-[#364153] focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/25">
                <option value="" @selected($roleFilter === null)>Semua role</option>
                <option value="customer" @selected($roleFilter === 'customer')>Customer</option>
                <option value="seller" @selected($roleFilter === 'seller')>Seller</option>
                <option value="mitra" @selected($roleFilter === 'mitra')>Mitra</option>
                <option value="admin" @selected($roleFilter === 'admin')>Admin</option>
            </select>
        </form>

        <div class="mt-8 overflow-hidden rounded-[24px] border-2 border-[#f3f4f6] bg-white shadow-lg">
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
                        @include('surprisebite.admin.partials.users-tbody', ['users' => $users, 'orderCounts' => $orderCounts])
                    </tbody>
                </table>
            </div>
        </div>

        @if ($users->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <dialog id="dlg-user" class="w-full max-w-md rounded-3xl border-2 border-slate-200 p-0 shadow-2xl backdrop:bg-slate-900/40">
        <form id="form-user" method="post" class="p-6">
            @csrf
            @method('PUT')
            <h3 class="text-xl font-black text-slate-900">Edit user</h3>
            <div class="mt-4 space-y-3">
                <label class="block text-sm font-bold text-slate-700">Name
                    <input name="name" id="u-name" required class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Email
                    <input name="email" id="u-email" type="email" required class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <label class="block text-sm font-bold text-slate-700">Phone
                    <input name="phone" id="u-phone" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                </label>
                <div id="u-role-wrap">
                    <label class="block text-sm font-bold text-slate-700">Role
                        <select name="role" id="u-role" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold">
                            <option value="customer">Customer</option>
                            <option value="seller">Seller</option>
                            <option value="mitra">Mitra</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('dlg-user').close()" class="rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-black text-slate-800">Cancel</button>
                <button type="submit" class="rounded-xl bg-[#2563eb] px-5 py-2.5 text-sm font-black text-white hover:bg-blue-700">Save</button>
            </div>
        </form>
    </dialog>

    <script>
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.edit-user');
            if (!btn || !btn.closest('#rt-users-tbody')) return;
            var d = JSON.parse(btn.getAttribute('data-json'));
            var f = document.getElementById('form-user');
            f.action = '{{ url('/admin/users') }}/' + d.id;
            document.getElementById('u-name').value = d.name || '';
            document.getElementById('u-email').value = d.email || '';
            document.getElementById('u-phone').value = d.phone || '';
            var rw = document.getElementById('u-role-wrap');
            if (d.role === 'admin') {
                rw.style.display = 'none';
                document.getElementById('u-role').removeAttribute('name');
            } else {
                rw.style.display = 'block';
                document.getElementById('u-role').setAttribute('name', 'role');
                document.getElementById('u-role').value = (d.role === 'seller' || d.role === 'mitra') ? d.role : 'customer';
            }
            document.getElementById('dlg-user').showModal();
        });
    </script>
</x-layouts.admin>
