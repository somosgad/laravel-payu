<?php

namespace SomosGAD_\LaravelPayU;

use GuzzleHttp\Client;

class LaravelPayU
{
    public static function createPayment() {
        $http = new Client;
        $url = 'https://api.paymentsos.com/payments';
        $json = [
            'amount' => 2000,
            'currency' => 'USD',
        ];
        $headers = [
            'app-id' => env('PAYU_APP_ID'),
            'private_key' => env('PAYU_PRIVATE_KEY'),
            'api-version' => '1.3.0',
            'x-payments-os-env' => env('PAYU_ENV'),
            'idempotency_key' => 'cust-34532-trans-001356-p',
        ];
        $response = $http->post($url, compact('json', 'headers'));

        return json_decode((string) $response->getBody(), true);
    }
}
