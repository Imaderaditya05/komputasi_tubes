<?php

namespace App\Http\Controllers;

use App\Models\AdminRestaurant;
use App\Models\CheckoutOrder;
use App\Models\CustomerPartnerReport;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\CatalogRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerReportController extends Controller
{
    /**
     * Laporan mitra dari halaman pesanan selesai — partner & menu diambil dari order + katalog (bukan input klien).
     */
    public function storeFromCompletedOrder(Request $request, string $publicOrderId): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        /** @var CheckoutOrder|null $order */
        $order = CheckoutOrder::query()->where('public_order_id', $publicOrderId)->first();
        if ($order === null) {
            abort(404);
        }

        if ($order->customer_email !== $user->email) {
            abort(403);
        }

        if ($order->fulfillment_status !== 'completed') {
            return redirect()
                ->route('orders.track', ['publicOrderId' => $publicOrderId])
                ->withErrors(['message' => 'Laporan hanya bisa dikirim setelah pesanan selesai.'], 'reportPartner')
                ->withInput();
        }

        $slug = (string) ($order->box_slug ?? '');
        if ($slug === '') {
            abort(404);
        }

        $validated = $request->validateWithBag('reportPartner', [
            'category' => ['nullable', 'string', 'max:64'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $catalog = app(CatalogRepository::class)->getCatalog();
        $box = $this->findBoxInCatalog($catalog, $slug);
        if ($box === null) {
            abort(404);
        }

        $displayName = trim((string) $order->restaurant_name) !== ''
            ? (string) $order->restaurant_name
            : (string) ($box['title'] ?? 'Restoran');

        $this->createPartnerReportForBox(
            $user,
            $catalog,
            $box,
            $slug,
            $displayName,
            $validated['category'] ?? null,
            (string) $validated['message'],
        );

        $to = route('orders.track', ['publicOrderId' => $publicOrderId]).'#order-partner-report';

        return redirect()->to($to)
            ->with('partner_report_status', 'Laporan Anda telah dikirim. Tim kami akan meninjaunya.');
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @return array<string, mixed>|null
     */
    private function findBoxInCatalog(array $catalog, string $slug): ?array
    {
        foreach ($catalog['boxes'] as $b) {
            if (($b['slug'] ?? '') === $slug) {
                return $b;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  array<string, mixed>  $box
     */
    private function createPartnerReportForBox(
        User $user,
        array $catalog,
        array $box,
        string $boxSlug,
        string $restaurantDisplayName,
        ?string $category,
        string $message,
    ): void {
        $partnerKey = (string) ($box['restaurant_id'] ?? '');
        if ($partnerKey === '') {
            abort(500, 'Konfigurasi katalog tidak memuat partner untuk menu ini.');
        }

        $mitraId = null;
        $adminId = null;

        if (preg_match('/^mitra-(\d+)$/', $partnerKey, $m)) {
            $mitraId = (int) $m[1];
            if (! Restaurant::query()->whereKey($mitraId)->exists()) {
                abort(404, 'Toko mitra tidak ditemukan.');
            }
        } else {
            $adminId = AdminRestaurant::query()->where('slug', $partnerKey)->value('id');
        }

        $inCatalog = false;
        foreach ($catalog['restaurants'] as $r) {
            if ((string) ($r['id'] ?? '') === $partnerKey) {
                $inCatalog = true;
                break;
            }
        }

        if (! $inCatalog) {
            abort(404, 'Toko tidak valid di katalog.');
        }

        CustomerPartnerReport::query()->create([
            'user_id' => $user->id,
            'partner_key' => $partnerKey,
            'mitra_restaurant_id' => $mitraId,
            'admin_restaurant_id' => $adminId,
            'restaurant_display_name' => $restaurantDisplayName,
            'box_slug' => $boxSlug,
            'category' => $category ?: null,
            'message' => $message,
            'status' => 'pending',
        ]);
    }
}
