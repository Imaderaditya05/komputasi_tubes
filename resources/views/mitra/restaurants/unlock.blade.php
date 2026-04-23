<x-layouts.app title="Unlock Restaurant">
    <div class="py-20 bg-gray-50 min-h-screen flex flex-col items-center">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden ring-1 ring-gray-900/5">
            <div class="bg-gray-900 py-6 px-8 text-center">
                <svg class="mx-auto h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h2 class="mt-4 text-2xl font-bold text-white">Unlock Restoran</h2>
                <p class="text-gray-400 mt-1">{{ $restaurant->name }}</p>
            </div>
            
            <div class="p-8">
                <form action="{{ route('mitra.restaurants.unlock', $restaurant) }}" method="POST">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Masukkan PIN Restoran</label>
                        <input type="password" name="pin" autofocus required 
                            class="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-center text-xl tracking-[0.5em]">
                        @error('pin')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <button type="submit" class="mt-6 w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Buka Kunci (Unlock)
                    </button>
                </form>

                <div class="mt-6 text-center text-sm">
                    <a href="{{ route('mitra.dashboard') }}" class="font-medium text-gray-500 hover:text-gray-900">
                        &larr; Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
