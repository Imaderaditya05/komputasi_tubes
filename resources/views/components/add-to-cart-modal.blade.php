@props(['boxSlug', 'boxTitle' => ''])

<button type="button" 
        @click="showAddToCartModal = true"
        class="inline-flex items-center gap-2 rounded-lg bg-green-500 hover:bg-green-600 px-4 py-2 text-sm font-semibold text-white transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Add to Cart
</button>

<div x-show="showAddToCartModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.outside="showAddToCartModal = false">
    <div class="bg-white rounded-lg p-6 w-96 shadow-xl">
        <h3 class="text-xl font-bold text-gray-900 mb-4">🛒 Add to Cart</h3>
        
        <p class="text-gray-600 mb-4">{{ $boxTitle }}</p>
        
        <form action="{{ route('cart.add') }}" method="POST">
            @csrf
            <input type="hidden" name="box_slug" value="{{ $boxSlug }}">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Quantity</label>
                <div class="flex items-center gap-3">
                    <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="bg-gray-200 hover:bg-gray-300 rounded px-3 py-2 text-sm font-semibold">−</button>
                    <input type="number" x-model.number="quantity" name="quantity" min="1" max="100" class="w-20 border border-gray-300 rounded px-3 py-2 text-center" required>
                    <button type="button" @click="quantity = Math.min(100, quantity + 1)" class="bg-gray-200 hover:bg-gray-300 rounded px-3 py-2 text-sm font-semibold">+</button>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" @click="showAddToCartModal = false" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-lg transition">
                    Tambah ke Cart
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        window.Alpine.data('addToCart', () => ({
            showAddToCartModal: false,
            quantity: 1,
        }));
    }
});
</script>
