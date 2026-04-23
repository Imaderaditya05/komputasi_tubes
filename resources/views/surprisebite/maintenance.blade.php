<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance • SurpriseBite</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-6">
    <div class="max-w-lg rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-xl">
        <p class="text-sm font-bold uppercase tracking-wider text-amber-600">Maintenance mode</p>
        <h1 class="mt-3 text-2xl font-black text-slate-900">Kami sedang melakukan pemeliharaan</h1>
        <p class="mt-3 text-slate-600">Platform sementara tidak dapat diakses. Silakan coba lagi nanti.</p>
    </div>
</body>
</html>
