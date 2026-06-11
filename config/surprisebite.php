<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pickup validation (mitra)
    |--------------------------------------------------------------------------
    |
    | Pesanan pickup aktif (konfirmasi → diterima → disiapkan → siap ambil): mitra punya jendela
    | konfirmasi sebelum kedaluwarsa. Kebijakan final mengikuti pemeriksaan di backend.
    |
    */

    'pickup_validation_window_minutes' => max(1, min(24 * 60, (int) env('PICKUP_VALIDATION_WINDOW_MINUTES', 15))),

];
