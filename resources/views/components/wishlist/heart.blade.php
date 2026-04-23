@props([
    'type' => 'menu',
    'targetKey' => '',
    'active' => false,
    'class' => '',
])

@php
    $user = auth()->user();
    $isCustomer = $user && $user->role === 'customer';
    $isGuest = ! $user;
@endphp

@if ($isCustomer)
    <form method="post" action="{{ route('wishlist.toggle') }}" class="inline">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}" />
        <input type="hidden" name="key" value="{{ $targetKey }}" />
        <button
            type="submit"
            class="{{ $class }} inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/95 shadow ring-1 ring-black/10 backdrop-blur-sm hover:bg-white {{ $active ? 'text-rose-600' : 'text-slate-600 hover:text-rose-500' }}"
            title="{{ $active ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}"
            aria-label="{{ $active ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}"
        >
            @if ($active)
                <x-sb.icon name="heart-solid" class="h-5 w-5" />
            @else
                <x-sb.icon name="heart" class="h-5 w-5" />
            @endif
        </button>
    </form>
@elseif ($isGuest)
    <a
        href="{{ route('login') }}"
        class="{{ $class }} inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-slate-600 shadow ring-1 ring-black/10 backdrop-blur-sm hover:bg-white hover:text-rose-500"
        title="Login untuk wishlist"
        aria-label="Login untuk wishlist"
    >
        <x-sb.icon name="heart" class="h-5 w-5" />
    </a>
@else
    <span
        class="{{ $class }} inline-flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-full bg-white/80 text-slate-400 opacity-60 ring-1 ring-black/5"
        title="Wishlist hanya untuk akun pelanggan"
        aria-hidden="true"
    >
        <x-sb.icon name="heart" class="h-5 w-5" />
    </span>
@endif
