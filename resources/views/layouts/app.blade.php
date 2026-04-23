<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SurpriseBite Test' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            /* Mencegah icon SVG membesar jadi giant hexagon sebelum Tailwind termuat */
            svg:not([width]) { max-width: 2.5rem; max-height: 2.5rem; }
        </style>
    @endif
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <header class="flex items-center justify-between py-3">
                <a href="{{ route('home') }}" class="flex flex-col justify-center gap-0.5">
                    <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="SurpriseBite Logo" class="h-10 sm:h-12 w-auto object-contain" />
                    <div class="hidden sm:block text-[10px] sm:text-xs text-slate-500 font-medium leading-none ml-2 tracking-wide">Save food, get surprise meals!</div>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-bold text-slate-600 sm:flex">
                    <a href="{{ route('browse') }}" class="hover:text-[#00a63e] transition">Browse</a>
                    <a href="{{ route('impact') }}" class="hover:text-[#00a63e] transition">Impact</a>
                    <a href="{{ route('about') }}" class="hover:text-[#00a63e] transition">About</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}"
                       class="hidden rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-200 sm:inline-flex transition">
                        Admin
                    </a>
                    <button type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#00a63e] to-[#00c950] px-5 py-2 text-sm font-bold text-white shadow-md hover:scale-105 active:scale-95 transition-all">
                        Login
                    </button>
                </div>
            </header>
        </div>
    </div>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="SurpriseBite Logo" class="h-6 sm:h-7 w-auto object-contain grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition" />
                    <span class="text-slate-400 ml-2">•</span>
                    <span>Mengurangi food waste dari restoran sekitar.</span>
                </div>
                <div class="text-sm font-medium text-slate-500">
                    © {{ date('Y') }} SurpriseBite
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

