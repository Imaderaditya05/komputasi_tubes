<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CheckoutOrder;
use Illuminate\Http\Request;

trait AuthorizesCustomerCheckoutOrder
{
    protected function authorizeCustomerCheckoutOrder(Request $request, CheckoutOrder $order): void
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer' || $order->customer_email !== $user->email) {
            abort(403);
        }
    }
}
