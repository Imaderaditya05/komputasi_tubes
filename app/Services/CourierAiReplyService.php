<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CourierAiReplyService
{
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
        $system = $this->systemPrompt($order);

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
                Log::warning('OpenAI courier chat HTTP error', [
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
            Log::warning('OpenAI courier chat exception', ['e' => $e->getMessage()]);

            return $this->fallbackReply($order, $messages);
        }
    }

    private function systemPrompt(CheckoutOrder $order): string
    {
        $status = (string) ($order->fulfillment_status ?? '');
        $method = $order->fulfillment_method === 'delivery' ? 'pengiriman (delivery)' : 'pengambilan di restoran (pickup)';

        return <<<PROMPT
Kamu adalah BOT CHAT OTOMATIS (chatbot kurir/logistik SurpriseBite), bukan orang sungguhan. Jangan menyebut diri sebagai "AI"—sebutlah bot atau asisten otomatis bila perlu. WAJIB bahasa Indonesia.

Data pesanan (jangan mengarang nomor telepon, alamat lengkap pelanggan, atau data sensitif lain):
- ID: {$order->public_order_id}
- Restoran: {$order->restaurant_name}
- Mystery box: {$order->box_title}
- Status saat ini: {$status}
- Metode: {$method}
- Jendela waktu: {$order->pickup_time}

Cara menjawab (penting):
- Baca seluruh riwayat chat; tanggapi inti pertanyaan TERBARU user dengan natural dan langsung pada pokoknya.
- Usahakan membantu BERBAGAI pertanyaan pelanggan: status, cara kerja SurpriseBite, estimasi/perkiraan logistik, restoran secara umum, mystery box secara umum, keluhan umum yang dilandasi data pesanan di atas—asalkan tetap sopan dan faktual. Jangan menolak hanya karena pertanyaannya bukan kata kunci “pengiriman”.
- Jika pertanyaan jauh dari kerangka layanan SurpriseBite/pesanan ini, jawab singkat lalu arahkan ke halaman lacak atau CS sesuai kebutuhan.
- Ubah gaya kalimat tiap balasan; jangan pembuka templat berulang.
- Panjang fleksibel sesuai kebutuhan jawaban.
- Jangan jamjanji waktu tepat atau janji hukum; perkiraan/umum bila perlu.
- Detail isi mystery box spesifik tidak bisa dijamin; arahkan ke kebijakan/halaman pesanan bila ditanya itu.
- Bila tidak cukup data: akui ringkas—tanpa paragraf panjang berulang.
PROMPT;
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

        $status = (string) ($order->fulfillment_status ?? '');
        $method = $order->fulfillment_method === 'delivery' ? 'pengiriman (delivery)' : 'pengambilan di restoran (pickup)';
        $statusHint = match ($status) {
            'completed' => 'Status pesanan sudah selesai.',
            'ready' => 'Pesanan sudah siap — untuk delivery, kurir biasanya segera menjemput dan mengantar.',
            'preparing' => 'Tim restoran sedang menyiapkan pesanan Anda.',
            'received', 'pending_confirmation' => 'Pesanan sudah diterima sistem/restoran dan akan diproses.',
            default => 'Silakan pantau pembaruan status di halaman ini.',
        };

        if (
            str_contains($last, 'halo') || str_contains($last, 'hai') || str_contains($last, 'hi ')
            || str_contains($last, 'selamat') || $last === 'hai' || $last === 'halo'
        ) {
            return 'Halo! Saya bantu info seputar pengantaran/pengambilan untuk pesanan '.$order->public_order_id.' dari '.$order->restaurant_name.'. Mau tanya apa—lokasi di peta, perkiraan waktu, atau arti status saat ini?';
        }
        if (str_contains($last, 'apa isi') || str_contains($last, 'isi box') || str_contains($last, 'mystery')) {
            return 'Isi mystery box memang dirahasiakan sampai Anda terima—saya tidak bisa cek isi spesifiknya. Yang pasti paket dari '.$order->restaurant_name.' mengikuti deskripsi box di halaman pesanan. Ada hal lain tentang pengiriman atau jadwal?';
        }
        if (str_contains($last, 'mana') || str_contains($last, 'dimana') || str_contains($last, 'lokasi')) {
            return 'Untuk posisi kurir: pakai peta di halaman lacak jika sedang delivery dan peta aktif. '.$statusHint.' Kalau belum muncul, biasanya pelacakan mengikuti tahap status pesanan.';
        }
        if (str_contains($last, 'lama') || str_contains($last, 'berapa lama') || str_contains($last, 'kapan') || str_contains($last, 'cepat')) {
            return 'Lama pengantaran bergantung antrian restoran dan rute. '.$statusHint.' Jendela yang tertera: '.($order->pickup_time ?: '(cek detail pesanan)').'. Tanpa nomor pesanan di sistem kurir, saya tidak bisa pastikan jam tepat.';
        }
        if (str_contains($last, 'batal') || str_contains($last, 'cancel') || str_contains($last, 'refund')) {
            return 'Pembatalan dan refund biasanya lewat menu pesanan atau CS SurpriseBite—saya dari sisi chat kurir tidak bisa memproses pembatalan. Status terkini: '.$status.'.';
        }
        if (str_contains($last, 'komplain') || str_contains($last, 'rusak') || str_contains($last, 'salah')) {
            return 'Maaf mendengar kendalanya. Untuk komplain resmi, hubungi CS SurpriseBite atau sertakan detail di form bantuan agar bisa ditindak. Secara singkat: pesanan '.$order->public_order_id.', status '.$status.'.';
        }
        if (str_contains($last, 'terima kasih') || str_contains($last, 'thanks') || str_contains($last, 'makasih')) {
            return 'Sama-sama! Kalau nanti ada pertanyaan lain soal pengiriman atau status, tulis saja di sini.';
        }
        if (str_contains($last, 'status') || str_contains($last, 'sudah') && str_contains($last, 'jalan')) {
            return $statusHint.' Ringkas: pesanan '.$order->public_order_id.' ('.$method.'). Detail langkah ada di halaman lacak.';
        }
        if (str_contains($last, 'berapa') || str_contains($last, 'ongkir') || str_contains($last, 'harga')) {
            return 'Total dan ongkir sudah tercatat saat checkout—saya tidak melihat rincian pembayaran di chat kurir. Untuk invoice/detail biaya, cek halaman pesanan atau riwayat transaksi.';
        }

        $lastUserRaw = '';
        foreach (array_reverse($messages) as $m) {
            if (($m['role'] ?? '') === 'user' && isset($m['content'])) {
                $lastUserRaw = trim((string) $m['content']);
                break;
            }
        }
        $snippet = $lastUserRaw !== '' ? mb_substr($lastUserRaw, 0, 120) : 'pertanyaan Anda';

        return 'Terkait «'.$snippet.'»: '.$statusHint.' Bot ini bisa membantu banyak topik pelanggan seputar pesanan '.$order->public_order_id.' ('.$method.') dan layanan—perjelas saja atau cek juga halaman lacak / CS SurpriseBite bila perlu konfirmasi resmi.';
    }
}
