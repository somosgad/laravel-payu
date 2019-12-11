<?php

namespace SomosGAD_\LaravelPayU;

use GuzzleHttp\Client;

class LaravelPayU
{
    private $http;
    private $headers;

    public function __construct()
    {
        $this->http = new Client;
        $this->headers = [
            'app-id' => getenv('PAYU_APP_ID'),
            'private_key' => getenv('PAYU_PRIVATE_KEY'),
            'api-version' => '1.3.0',
            'x-payments-os-env' => getenv('PAYU_ENV'),
        ];
    }

    private function format($response)
    {
        $guzzleBodyStream = $response->getBody();
        $json_string = (string) $guzzleBodyStream;
        $array = json_decode($json_string, true);
        return $array;
    }

    public function createToken()
    {
        $url = 'https://api.paymentsos.com/tokens';
        $headers = $this->headers;
        $response = $this->http->post($url, compact('headers', 'json'));
        return $this->format($response);
    }

    public function createPayment()
    {
        $url = 'https://api.paymentsos.com/payments';
        $headers = array_merge($this->headers, [
            'idempotency_key' => 'cust-34532-trans-001356-p',
        ]);
        $json = [
            'amount' => 2000,
            'currency' => 'USD',
        ];
        $response = $this->http->post($url, compact('headers', 'json'));
        return $this->format($response);
    }

    public function createCharge($paymentId, $cvv, $token)
    {
        $url = "https://api.paymentsos.com/payments/{{paymentid}}/charges";
        $headers = array_merge($this->headers, [
            'idempotency_key' => 'cust-34532-trans-001356-p',
        ]);
        $json = [
            'payment_method' => [
                'credit_card_cvv' => $cvv,
                'token' => $token,
                'type' => 'tokenized',
            ],
        ];
        $response = $this->http->post($url, compact('headers', 'json'));
        return $this->format($response);
    }
}
