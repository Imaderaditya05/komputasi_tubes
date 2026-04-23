@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-red-50 border border-red-200 rounded-lg p-8 text-center">
            <div class="mb-4">
                <svg class="w-16 h-16 mx-auto text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-red-800 mb-2">Pembayaran Gagal ❌</h1>
            
            <div class="bg-white rounded p-4 mb-6 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 text-sm">Order ID</p>
                        <p class="font-semibold text-gray-800">{{ $order->public_order_id }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Amount</p>
                        <p class="font-semibold text-gray-800">Rp {{ number_format($order->amount_idr, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-500 text-sm">Box</p>
                        <p class="font-semibold text-gray-800">{{ $order->box_title }} - {{ $order->restaurant_name }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-500 text-sm">Status</p>
                        <p class="font-semibold text-red-600">{{ $order->xendit_status ?? 'FAILED' }}</p>
                    </div>
                </div>
            </div>

            <p class="text-gray-600 mb-6">
                Pembayaran Anda tidak berhasil. Silakan coba lagi atau gunakan metode pembayaran lain.
            </p>

            <div class="flex gap-3">
                <a href="{{ route('payment.checkout', $order->public_order_id) }}" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
                    Coba Lagi
                </a>
                <a href="{{ route('home') }}" class="flex-1 bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700 transition">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
