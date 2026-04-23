@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
            <div class="mb-4">
                <svg class="w-16 h-16 mx-auto text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-green-800 mb-2">Pembayaran Berhasil! 🎉</h1>
            
            <div class="bg-white rounded p-4 mb-6 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 text-sm">Order ID</p>
                        <p class="font-semibold text-gray-800 break-all">{{ $order->public_order_id }}</p>
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
                        <p class="font-semibold text-green-600">{{ $order->xendit_status ?? 'PENDING' }}</p>
                    </div>
                </div>
            </div>

            <p class="text-gray-600 mb-6">
                Terima kasih telah melakukan pembayaran. Invoice telah dikirim ke email Anda di <strong>{{ $order->customer_email }}</strong>.
            </p>

            <div class="flex gap-3">
                <a href="{{ route('home') }}" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
                    Kembali ke Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
