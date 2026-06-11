<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminUserListService
{
    /**
     * @return array{users: LengthAwarePaginator, orderCounts: array<int, int>, roleFilter: ?string, q: string, activeListFilter: bool}
     */
    public function paginatedForAdmin(Request $request, int $perPage = 20): array
    {
        $q = trim((string) $request->query('q', ''));
        $roleFilter = $this->normalizeRoleFilter($request->query('role'));

        $base = User::query()->orderByDesc('created_at');

        if ($q !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $base->where(function (Builder $w) use ($term): void {
                $w->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        $this->applyRoleScope($base, $roleFilter);
        $this->applyActiveListScope($base, $request);

        $users = $base->paginate($perPage)->withQueryString();
        $orderCounts = $this->orderCountsForUsers($users);

        return [
            'users' => $users,
            'orderCounts' => $orderCounts,
            'roleFilter' => $roleFilter,
            'q' => $q,
            'activeListFilter' => $this->wantsActiveListOnly($request),
        ];
    }

    /**
     * @return array{total: int, customers: int, sellers: int, active: int, pending_mitra: int}
     */
    public function stats(): array
    {
        $activeUsers = Schema::hasColumn('users', 'is_active')
            ? User::where(function (Builder $q): void {
                $q->where('is_active', true)->orWhereNull('is_active');
            })->count()
            : User::count();

        $pendingMitra = 0;
        if (Schema::hasColumn('users', 'mitra_approval_status')) {
            $pendingMitra = (int) User::query()
                ->where('role', 'mitra')
                ->where('mitra_approval_status', 'pending')
                ->count();
        }

        return [
            'total' => User::count(),
            'customers' => User::where('role', 'customer')->count(),
            'sellers' => User::whereIn('role', ['seller', 'mitra'])->count(),
            'active' => $activeUsers,
            'pending_mitra' => $pendingMitra,
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, User>|iterable<int, User>  $users
     * @return array<int, int>
     */
    public function orderCountsForUsers(iterable $users): array
    {
        $orderCounts = [];
        if (! Schema::hasTable('checkout_orders')) {
            return $orderCounts;
        }
        foreach ($users as $u) {
            $orderCounts[$u->id] = CheckoutOrder::where('customer_email', $u->email)->count();
        }

        return $orderCounts;
    }

    public function normalizeRoleFilter(mixed $roleParam): ?string
    {
        $r = is_string($roleParam) ? $roleParam : null;
        if (in_array($r, ['customer', 'admin', 'seller'], true)) {
            return $r;
        }

        return null;
    }

    private function applyRoleScope(Builder $base, ?string $roleFilter): void
    {
        if ($roleFilter === null) {
            return;
        }
        if ($roleFilter === 'seller') {
            $base->whereIn('role', ['seller', 'mitra']);

            return;
        }
        $base->where('role', $roleFilter);
    }

    public function wantsActiveListOnly(Request $request): bool
    {
        return $request->boolean('active');
    }

    private function applyActiveListScope(Builder $base, Request $request): void
    {
        if (! $this->wantsActiveListOnly($request)) {
            return;
        }
        if (! Schema::hasColumn('users', 'is_active')) {
            return;
        }
        $base->where(function (Builder $q): void {
            $q->where('is_active', true)->orWhereNull('is_active');
        });
    }
}
