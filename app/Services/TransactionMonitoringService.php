<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransactionMonitoringService
{
    public const COMPLETED_STATUSES = ImpactMetricsService::IMPACT_STATUSES;

    public static function displayBucket(?string $paymentStatus): string
    {
        if ($paymentStatus === null || $paymentStatus === '') {
            return 'pending';
        }

        if (in_array($paymentStatus, self::COMPLETED_STATUSES, true)) {
            return 'completed';
        }

        if ($paymentStatus === 'pending') {
            return 'pending';
        }

        return 'failed';
    }

    public function summary(): array
    {
        $base = CheckoutOrder::query();

        $completed = (clone $base)->whereIn('payment_status', self::COMPLETED_STATUSES);
        $pending = (clone $base)->where(function (Builder $q): void {
            $q->whereNull('payment_status')
                ->orWhere('payment_status', 'pending');
        });
        $failed = (clone $base)->where(function (Builder $q): void {
            $q->whereNotNull('payment_status')
                ->whereNotIn('payment_status', array_merge(self::COMPLETED_STATUSES, ['pending']));
        });

        return [
            'revenue_idr' => (int) (clone $completed)->sum('amount_idr'),
            'completed' => (int) (clone $completed)->count(),
            'pending' => (int) (clone $pending)->count(),
            'failed' => (int) (clone $failed)->count(),
        ];
    }

    public function filteredOrdersQuery(string $search = '', ?string $statusFilter = null): Builder
    {
        $q = CheckoutOrder::query()
            ->with('customer')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $q->where(function (Builder $inner) use ($term): void {
                $inner->where('public_order_id', 'like', $term)
                    ->orWhere('midtrans_transaction_id', 'like', $term)
                    ->orWhere('restaurant_name', 'like', $term)
                    ->orWhere('customer_email', 'like', $term)
                    ->orWhereHas('customer', function (Builder $c) use ($term): void {
                        $c->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    })
                    ->orWhereHas('user', function (Builder $c) use ($term): void {
                        $c->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        if ($statusFilter === 'completed' || $statusFilter === 'pending' || $statusFilter === 'failed') {
            $q->where(function (Builder $inner) use ($statusFilter): void {
                if ($statusFilter === 'completed') {
                    $inner->whereIn('payment_status', self::COMPLETED_STATUSES);
                } elseif ($statusFilter === 'pending') {
                    $inner->whereNull('payment_status')
                        ->orWhere('payment_status', 'pending');
                } else {
                    $inner->whereNotNull('payment_status')
                        ->whereNotIn('payment_status', array_merge(self::COMPLETED_STATUSES, ['pending']));
                }
            });
        }

        return $q;
    }

    public function paginatedOrders(string $search = '', ?string $statusFilter = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filteredOrdersQuery($search, $statusFilter)
            ->paginate($perPage)
            ->withQueryString();
    }
}
