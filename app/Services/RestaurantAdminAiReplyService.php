<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestaurantAdminAiReplyService
{
    public function __construct(
        private OrderMapLocationService $orderMapLocation,
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function reply(CheckoutOrder $order, array $messages): string
    {
        $key = trim((string) config('services.openai.api_key', ''));
        if ($key === '') {
            return $this->fallbackReply($order, $messages);
        }

        $model = (string) config('services.openai.model', 'gpt-4o-mini');
        $map = $this->orderMapLocation->resolveForCheckoutOrder($order);
        $system = $this->systemPrompt($order, $map);

        $payload = [
            ['role' => 'system', 'content' => $system],
            ...$this->trimMessages($messages),
        ];

        try {
            $response = Http::timeout(45)
                ->withToken($key)
                ->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $payload,
                    'max_tokens' => 800,
                    'temperature' => 0.78,
                    'frequency_penalty' => 0.45,
                    'presence_penalty' => 0.25,
                ]);

            if (! $response->successful()) {
                Log::warning('OpenAI restaurant chat HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackReply($order, $messages);
            }

            $text = $response->json('choices.0.message.content');
            if (! is_string($text) || trim($text) === '') {
                return $this->fallbackReply($order, $messages);
            }

            return trim($text);
        } catch (\Throwable $e) {
            Log::warning('OpenAI restaurant chat exception', ['e' => $e->getMessage()]);

            return $this->fallbackReply($order, $messages);
        }
    }

    /**
     * @param  array{
     *   restaurantLat: ?float,
     *   restaurantLng: ?float,
     *   mapQuery: string,
     *   mapQueryAlternates: list<string>
     * }  $map
     */
    private function systemPrompt(CheckoutOrder $order, array $map): string
    {
        $status = (string) ($order->fulfillment_status ?? '');
        $method = $order->fulfillment_method === 'delivery' ? 'delivery' : 'pickup';
        $mapQuery = trim((string) ($map['mapQuery'] ?? ''));
        $lat = $map['restaurantLat'];
        $lng = $map['restaurantLng'];
        $coords = ($lat !== null && $lng !== null)
            ? sprintf('%.6f, %.6f', $lat, $lng)
            : 'tidak tersedia di data kami';
        $mapsUrl = $this->googleMapsSearchUrl($mapQuery !== '' ? $mapQuery : $order->restaurant_name.', Indonesia');

        return <<<PROMPT
Kamu adalah BOT CHAT OTOMATIS yang mewakili sisi {$order->restaurant_name} di SurpriseBite (bukan orang sungguhan). Jangan menyebut diri sebagai "AI"—sebutlah bot atau asisten toko otomatis bila perlu. WAJIB bahasa Indonesia.

Data pesanan (jangan mengarang nomor telepon pribadi, alamat pengiriman lengkap pelanggan, atau data sensitif lain):
- ID: {$order->public_order_id}
- Restoran: {$order->restaurant_name}
- Mystery box: {$order->box_title}
- Status: {$status}
- Metode: {$method}
- Jendela waktu: {$order->pickup_time}
- Query pencarian Maps (pakai persis jika user minta lokasi): "{$mapQuery}"
- Koordinat toko (referensi): {$coords}

Cara menjawab (penting):
- Baca riwayat chat; jawab langsung pertanyaan terbaru dengan natural.
- Usahakan membantu BERBAGAI pertanyaan pelanggan: restoran, pesanan, platform SurpriseBite, cara pickup/delivery, FAQ umum—selama bisa dilandasi data di atas dan tetap sopan/jujur tentang batasmu.
- Jika pertanyaan di luar konteks atau butuh orang sungguhan, akui ringkas dan arahkan CS platform atau halaman lacak.
- Hindari pembuka templat berulang; panjang jawaban fleksibel.
- Lokasi/alamat/dimana/maps: ringkas + tautan persis ini (satu baris URL utuh): {$mapsUrl}
- Status pakai "{$status}", jelaskan dengan bahasa pelanggan.
PROMPT;
    }

    private function googleMapsSearchUrl(string $query): string
    {
        $q = trim($query);

        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($q !== '' ? $q : 'Indonesia');
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return list<array{role: string, content: string}>
     */
    private function trimMessages(array $messages): array
    {
        $out = [];
        foreach (array_slice($messages, -20) as $m) {
            $role = $m['role'] ?? '';
            $content = isset($m['content']) ? (string) $m['content'] : '';
            if (! in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }
            $out[] = ['role' => $role, 'content' => mb_substr($content, 0, 2000)];
        }

        return $out;
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    private function fallbackReply(CheckoutOrder $order, array $messages): string
    {
        $last = '';
        foreach (array_reverse($messages) as $m) {
            if (($m['role'] ?? '') === 'user' && isset($m['content'])) {
                $last = mb_strtolower((string) $m['content']);
                break;
            }
        }

        $method = $order->fulfillment_method === 'delivery' ? 'delivery' : 'pickup';

        $map = $this->orderMapLocation->resolveForCheckoutOrder($order);
        $mapQuery = trim((string) ($map['mapQuery'] ?? ''));
        $q = $mapQuery !== '' ? $mapQuery : $order->restaurant_name.', Indonesia';
        $mapsUrl = $this->googleMapsSearchUrl($q);

        $status = (string) ($order->fulfillment_status ?? '');
        $statusHint = match ($status) {
            'completed' => 'Status pesanan sudah selesai.',
            'ready' => 'Pesanan sudah siap diambil atau menunggu kurir (tergantung metode Anda).',
            'preparing' => 'Tim kami sedang menyiapkan pesanan Anda.',
            'received', 'pending_confirmation' => 'Pesanan sudah kami terima dan akan segera diproses.',
            default => 'Silakan pantau pembaruan di halaman lacak pesanan.',
        };

        if (
            str_contains($last, 'halo') || str_contains($last, 'hai') || str_contains($last, 'selamat')
            || $last === 'hai' || $last === 'halo'
        ) {
            return 'Halo! Ada yang bisa kami bantu untuk '.$order->restaurant_name.'? Bisa tanya soal lokasi, jam layanan, status pesanan '.$order->public_order_id.', atau cara pickup/delivery.';
        }
        if (
            str_contains($last, 'alamat')
            || str_contains($last, 'lokasi')
            || str_contains($last, 'dimana')
            || str_contains($last, 'maps')
            || str_contains($last, 'google')
            || str_contains($last, 'letak')
        ) {
            return "Lokasi pencarian untuk {$order->restaurant_name} di Google Maps:\n{$mapsUrl}\n\n{$statusHint} Detail alamat pada peta mengikuti hasil Maps.";
        }
        if (str_contains($last, 'jam') || str_contains($last, 'buka') || str_contains($last, 'operasional')) {
            return 'Jam operasional pasti bisa berbeda per cabang; yang tercatat untuk jendela pesanan Anda: '.($order->pickup_time ?: 'lihat di detail order').'. Untuk konfirmasi jam buka spesifik, cross-check di profil restoran atau datang sesuai jendela tersebut.';
        }
        if (str_contains($last, 'apakah') && (str_contains($last, 'halal') || str_contains($last, 'alerg') || str_contains($last, 'pedas'))) {
            return 'Untuk jaminan halal/alergen/tingkat pedas, kebijakannya di masing-masing restoran. Yang pasti pesanan Anda adalah '.$order->box_title.' dari '.$order->restaurant_name.'. Untuk kebutuhan medis ketat, disarankan hubungi restoran langsung atau CS platform.';
        }
        if (str_contains($last, 'apa isi') || str_contains($last, 'mystery') || str_contains($last, 'isi box')) {
            return 'Isi konkret mystery box memang surprise—kami tidak sebut item per item di chat. Anda memesan: '.$order->box_title.'. '.$statusHint;
        }
        if (str_contains($last, 'status') || str_contains($last, 'sudah') && str_contains($last, 'siap')) {
            return $statusHint.' Pesanan '.$order->public_order_id.', metode '.$method.'. Per detail langkah, gunakan juga halaman lacak.';
        }
        if (str_contains($last, 'parkir') || str_contains($last, 'ambil')) {
            return 'Untuk pickup, biasanya ambil di titik restoran tertera di Maps/toko. '.$statusHint.' Tautan pencarian lokasi: '.$mapsUrl;
        }

        if (str_contains($last, 'terima kasih') || str_contains($last, 'thanks') || str_contains($last, 'makasih')) {
            return 'Sama-sama, senang membantu! Kalau ada pertanyaan lain tentang restoran atau pesanan, silakan.';
        }

        $lastUserRaw = '';
        foreach (array_reverse($messages) as $m) {
            if (($m['role'] ?? '') === 'user' && isset($m['content'])) {
                $lastUserRaw = trim((string) $m['content']);
                break;
            }
        }
        $snippet = $lastUserRaw !== '' ? mb_substr($lastUserRaw, 0, 120) : 'pertanyaan Anda';

        return 'Mengenai «'.$snippet.'»: '.$statusHint.' Bot ini bisa membantu banyak topik tentang '.$order->restaurant_name.', pesanan, atau SurpriseBite secara umum—perjelas saja atau gunakan tautan lokasi/maps bila Anda butuh alamat.';
    }
}
