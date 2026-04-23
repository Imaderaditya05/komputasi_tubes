@php
    use App\Services\TransactionMonitoringService;
    $paymentUi = static function (string $method): string {
        return match ($method) {
            'va' => 'Bank Transfer',
            'cod' => 'COD',
            'ewallet' => 'E-Wallet',
            default => $method !== '' ? strtoupper($method) : '—',
        };
    };
@endphp
@forelse ($orders as $order)
    @php
        $bucket = TransactionMonitoringService::displayBucket($order->payment_status);
        $trx = $order->midtrans_transaction_id
            ? (strlen($order->midtrans_transaction_id) > 18
                ? substr($order->midtrans_transaction_id, 0, 10) . '…'
                : $order->midtrans_transaction_id)
            : 'TRX-' . $order->id;
    @endphp
    <tr class="border-b border-[#f3f4f6] last:border-0 hover:bg-slate-50/80">
        <td class="px-4 py-5 align-middle">
            <span class="font-black text-[#00a63e]" title="{{ $order->midtrans_transaction_id ?? '—' }}">{{ $trx }}</span>
        </td>
        <td class="px-4 py-5 align-middle font-bold text-[#1e2939]">{{ $order->public_order_id }}</td>
        <td class="px-4 py-5 align-middle font-bold text-[#1e2939]">{{ $order->user?->name ?? $order->customer?->name ?? '—' }}</td>
        <td class="px-4 py-5 align-middle font-semibold text-[#4a5565]">{{ $order->restaurant_name }}</td>
        <td class="px-4 py-5 align-middle font-black text-[#00a63e]">{{ $money((int) $order->amount_idr) }}</td>
        <td class="px-4 py-5 align-middle">
            <span class="inline-flex rounded-full bg-[#f3f4f6] px-3 py-1 text-sm font-bold text-[#364153]">{{ $paymentUi($order->payment_method) }}</span>
        </td>
        <td class="px-4 py-5 align-middle">
            @if ($bucket === 'completed')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#dcfce7] px-3 py-1.5 text-sm font-black text-[#00a63e]"><x-sb.icon name="check-circle" class="h-4 w-4" /> Completed</span>
            @elseif ($bucket === 'pending')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#ffedd5] px-3 py-1.5 text-sm font-black text-[#f54900]"><x-sb.icon name="clock" class="h-4 w-4" /> Pending</span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#fee2e2] px-3 py-1.5 text-sm font-black text-[#e7000b]"><x-sb.icon name="x-circle" class="h-4 w-4" /> Failed</span>
            @endif
            @if ($order->payment_status)
                <span class="mt-1 block text-xs font-semibold text-slate-500">{{ $order->payment_status }}</span>
            @endif
        </td>
        <td class="px-4 py-5 align-middle">
            <div class="font-bold text-[#1e2939]">{{ $order->created_at?->timezone(config('app.timezone'))->format('Y-m-d') }}</div>
            <div class="text-sm font-semibold text-[#6a7282]">{{ $order->created_at?->timezone(config('app.timezone'))->format('H:i') }}</div>
        </td>
        <td class="px-4 py-5 align-middle">
            <a href="{{ route('admin.transactions', ['q' => $order->public_order_id]) }}"
               class="inline-flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#ecfdf5] text-[#00a63e] ring-1 ring-[#bbf7d0] hover:bg-[#d1fae5]"
               title="Filter ke order ini"><x-sb.icon name="eye" class="h-5 w-5" /></a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-6 py-16 text-center text-base font-semibold text-[#6a7282]">
            Belum ada transaksi atau tidak ada yang cocok dengan filter.
        </td>
    </tr>
@endforelse
