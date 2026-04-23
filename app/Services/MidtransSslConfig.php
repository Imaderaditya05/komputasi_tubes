<?php

namespace App\Services;

use Midtrans\Config;

/**
 * Laragon/Windows: php.ini sering mengarah ke cacert.pem yang tidak ada.
 * ini_set('curl.cainfo') tidak selalu dipakai oleh cURL — harus CURLOPT_CAINFO per request.
 */
class MidtransSslConfig
{
    public static function applyCurlCaBundle(): void
    {
        $path = config('services.midtrans.cacert_path');
        if (! is_string($path) || $path === '' || ! is_file($path)) {
            return;
        }

        Config::$curlOptions[CURLOPT_CAINFO] = $path;
    }
}
