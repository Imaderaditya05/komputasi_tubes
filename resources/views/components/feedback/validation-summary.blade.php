@props([
    /** @var 'public'|'admin' */
    'tone' => 'public',
])

@if ($errors->any())
    @php
        $toneKey = ($tone ?? 'public') === 'admin' ? 'admin' : 'public';
        $classes = match ($toneKey) {
            'admin' => 'mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-sm ring-1 ring-red-100/70',
            default => 'mb-4 mt-4 rounded-2xl border-2 border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-950 shadow-sm ring-1 ring-red-100/70 sm:mx-0',
        };
    @endphp
    <div
        class="{{ $classes }}"
        role="alert"
        aria-live="polite"
        data-validation-summary
    >
        <div class="flex items-start gap-3">
            <span class="mt-0.5 shrink-0 text-red-600" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1 space-y-2">
                <p class="font-black leading-snug">
                    Data tidak valid
                </p>
                <p class="{{ $toneKey === 'admin' ? 'text-xs font-semibold text-red-900/85' : 'text-sm font-semibold text-red-900/90' }}">
                    Beberapa kolom kosong atau formatnya salah. Kolom bertanda (*) biasanya wajib diisi.
                </p>
                @if ($errors->count() > 0)
                    <ul class="{{ $toneKey === 'admin' ? 'mt-2 list-disc space-y-1 pl-5 text-xs font-semibold text-red-900' : 'mt-2 list-disc space-y-1.5 pl-5 text-sm text-red-900' }}">
                        @foreach (array_unique($errors->all()) as $msg)
                            <li>{{ $msg }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <button
                type="button"
                class="shrink-0 rounded-lg p-1 font-bold text-red-700/80 transition hover:bg-red-100 hover:text-red-900"
                aria-label="Tutup"
                onclick="this.closest('[data-validation-summary]').remove()"
            >
                ×
            </button>
        </div>
    </div>
@endif
