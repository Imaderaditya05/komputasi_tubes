@props([
    'title' => 'Admin',
    'active' => 'dashboard',
])

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title }} • SurpriseBite Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        /* Smooth Global Hover Animasi */
        a, button, input[type="submit"], input[type="button"], .group, .card, .hover-target {
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
            transition-timing-function: cubic-bezier(0.25, 0.8, 0.25, 1);
            transition-duration: 300ms;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f4f6f8] antialiased [font-family:Inter,ui-sans-serif,system-ui,sans-serif]" data-admin-live="{{ $active }}">
    {{-- Mobile sidebar toggle (Figma admin shell 130:2) --}}
    <input type="checkbox" id="admin-nav-toggle" class="peer sr-only" aria-hidden="true" />

    <label for="admin-nav-toggle" class="fixed inset-0 z-20 bg-slate-900/50 opacity-0 pointer-events-none transition peer-checked:opacity-100 peer-checked:pointer-events-auto lg:hidden"></label>

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="fixed left-0 top-0 z-30 flex h-full w-[260px] flex-col border-r border-slate-700/60 bg-[#0f172a] text-slate-200 shadow-2xl transition-transform duration-200 -translate-x-full peer-checked:translate-x-0 lg:static lg:translate-x-0">
            <div class="flex h-16 items-center gap-3 border-b border-slate-700/60 px-5 transition hover:opacity-90">
                <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="SurpriseBite Logo" class="h-8 w-auto object-contain rounded-lg bg-white p-1 ring-1 ring-white/20 shadow-sm" />
                <div class="leading-tight mt-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Admin Panel</div>
                </div>
                <label for="admin-nav-toggle" class="ml-auto flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg bg-slate-800 text-slate-300 lg:hidden hover:bg-slate-700" aria-label="Tutup menu"><x-sb.icon name="x-mark" class="h-5 w-5" /></label>
            </div>

            <nav class="flex flex-1 flex-col gap-1 p-3">
                <p class="px-3 pb-2 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Menu</p>
                <a href="{{ route('admin.dashboard') }}"
                   class="admin-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition {{ $active === 'dashboard' ? 'bg-[#00a63e] text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-sb.icon name="chart-bar" class="h-5 w-5 shrink-0 opacity-90" aria-hidden="true" />
                    Dashboard
                </a>
                <a href="{{ route('admin.impact') }}"
                   class="admin-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition {{ $active === 'impact' ? 'bg-[#00a63e] text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-sb.icon name="globe" class="h-5 w-5 shrink-0 opacity-90" aria-hidden="true" />
                    Impact Tracker
                </a>
                <a href="{{ route('admin.transactions') }}"
                   class="admin-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition {{ $active === 'transactions' ? 'bg-[#00a63e] text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-sb.icon name="credit-card" class="h-5 w-5 shrink-0 opacity-90" aria-hidden="true" />
                    Transaction monitoring
                </a>
                <a href="{{ route('admin.restaurants') }}"
                   class="admin-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition {{ $active === 'restaurants' ? 'bg-[#00a63e] text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-sb.icon name="package" class="h-5 w-5 shrink-0 opacity-90" aria-hidden="true" />
                    Restaurants
                </a>
                <a href="{{ route('admin.users') }}"
                   class="admin-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition {{ $active === 'users' ? 'bg-[#00a63e] text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-sb.icon name="users" class="h-5 w-5 shrink-0 opacity-90" aria-hidden="true" />
                    User management
                </a>
                <a href="{{ route('admin.settings') }}"
                   class="admin-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition {{ $active === 'settings' ? 'bg-[#00a63e] text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-sb.icon name="tag" class="h-5 w-5 shrink-0 opacity-90" aria-hidden="true" />
                    System settings
                </a>

                <p class="mt-6 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Publik</p>
                <a href="{{ route('home') }}"
                   class="admin-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <span aria-hidden="true">↗</span>
                    Lihat situs
                </a>
            </nav>

            <div class="border-t border-slate-700/60 p-4">
                <div class="rounded-xl bg-slate-800/80 px-3 py-3 ring-1 ring-slate-700/50">
                    <div class="text-xs font-bold text-white">{{ $auth['name'] ?? 'Admin' }}</div>
                    <div class="mt-0.5 truncate text-[11px] text-slate-400">{{ $auth['email'] ?? '' }}</div>
                    <form method="post" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-slate-700 py-2 text-xs font-bold text-white transition hover:bg-slate-600">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col lg:ml-0">
            <header class="sticky top-0 z-10 flex items-center gap-4 border-b border-slate-200/80 bg-white/95 px-4 py-3 shadow-sm backdrop-blur-sm lg:px-8">
                <label for="admin-nav-toggle" class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden hover:bg-slate-50" aria-label="Buka menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>
                <div class="flex min-w-0 flex-1 flex-wrap items-center gap-3">
                    <div class="min-w-0">
                        <h1 class="text-lg font-black tracking-tight text-slate-900 lg:text-xl">{{ $title }}</h1>
                        <p class="hidden text-xs text-slate-500 sm:block">Panel pengelolaan SurpriseBite</p>
                    </div>
                    <div class="ml-auto flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/90 px-3 py-1.5 text-xs font-bold text-emerald-800 ring-1 ring-emerald-100/80" title="Data diperbarui otomatis">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span>Live</span>
                        <span id="rt-live-clock" class="tabular-nums text-emerald-700/90">—</span>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
    <script>
        document.querySelectorAll('.admin-sidebar-link').forEach(function (el) {
            el.addEventListener('click', function () {
                var t = document.getElementById('admin-nav-toggle');
                if (t) t.checked = false;
            });
        });
    </script>
</body>
</html>
