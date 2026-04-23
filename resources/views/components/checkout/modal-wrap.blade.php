<div {{ $attributes->merge(['class' => 'fixed inset-0 z-[100] flex min-h-[100dvh] items-start justify-center overflow-y-auto bg-black/55 px-3 py-6 backdrop-blur-[2px] sm:px-4 sm:py-10']) }}>
    <div class="my-auto w-full max-w-[520px] shrink-0">
        {{ $slot }}
    </div>
</div>
