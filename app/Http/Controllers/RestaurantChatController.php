<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCustomerCheckoutOrder;
use App\Models\CheckoutOrder;
use App\Services\RestaurantAdminAiReplyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantChatController extends Controller
{
    use AuthorizesCustomerCheckoutOrder;

    public function message(Request $request, string $publicOrderId): JsonResponse
    {
        $order = CheckoutOrder::query()->where('public_order_id', $publicOrderId)->first();
        if (! $order) {
            return response()->json(['error' => 'Pesanan tidak ditemukan.'], 404);
        }

        $this->authorizeCustomerCheckoutOrder($request, $order);

        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:24'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
        ]);

        $reply = app(RestaurantAdminAiReplyService::class)->reply($order, $validated['messages']);

        return response()->json(['reply' => $reply]);
    }
}
