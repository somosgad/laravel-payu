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
            'api-version' => '1.3.0',
            'app-id' => getenv('PAYU_APP_ID'),
            'x-payments-os-env' => getenv('PAYU_ENV'),
        ];
    }

    public function createToken(
        $card_number,
        $credit_card_cvv,
        $expiration_date,
        $holder_name,
        $token_type
    )
    {
        $url = 'https://api.paymentsos.com/tokens';
        $headers = array_merge($this->headers, [
            'public_key' => getenv('PAYU_PUBLIC_KEY'),
        ]);
        $json = compact(
            'card_number',
            'card_number' ,
            'credit_card_cvv',
            'expiration_date',
            'holder_name',
            'token_type',
        );
        $response = $this->http->post($url, compact('headers', 'json'));
        return $this->format($response);
    }

    public function createPayment()
    {
        $url = 'https://api.paymentsos.com/payments';
        $headers = array_merge($this->headers, [
            'idempotency_key' => 'cust-34532-trans-001356-p',
            'private_key' => getenv('PAYU_PRIVATE_KEY'),
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
        $url = "https://api.paymentsos.com/payments/$paymentId/charges";
        $headers = array_merge($this->headers, [
            'idempotency_key' => 'cust-34532-trans-001356-p2',
            'private_key' => getenv('PAYU_PRIVATE_KEY'),
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

    private function format($response)
    {
        $guzzleBodyStream = $response->getBody();
        $json_string = (string) $guzzleBodyStream;
        $array = json_decode($json_string, true);
        return $array;
    }
}
