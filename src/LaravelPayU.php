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
            'app-id' => getenv('PAYU_APP_ID'),
            'private_key' => getenv('PAYU_PRIVATE_KEY'),
            'api-version' => '1.3.0',
            'x-payments-os-env' => getenv('PAYU_ENV'),
            'idempotency_key' => 'cust-34532-trans-001356-p',
        ];
        $response = $http->post($url, compact('json', 'headers'));
        $guzzleBodyStream = $response->getBody();
        $json = (string) $guzzleBodyStream;
        $array = json_decode($json, true);
        return $array;
    }
}
