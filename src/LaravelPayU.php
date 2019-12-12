<?php

namespace SomosGAD_\LaravelPayU;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class LaravelPayU
{
    private $http;
    private $headers;
    private $private_key;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => 'https://api.paymentsos.com',
        ]);
        $this->headers = [
            'api-version' => '1.3.0',
            'app-id' => getenv('PAYU_APP_ID'),
            /* content type is auto-added by guzzle */
            // 'Content-Type' => 'application/json',
            'x-payments-os-env' => getenv('PAYU_ENV'),
        ];
        $this->private_key = getenv('PAYU_PRIVATE_KEY');
    }

    public function createAuthorization(string $paymentId, string $cvv, string $token) // string $reconciliation_id
    {
        $url = "payments/$paymentId/authorizations";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $json = [
            'payment_method' => [
                'credit_card_cvv' => $cvv,
                'token' => $token,
                'type' => 'tokenized',
            ],
            // 'reconciliation_id' => $reconciliation_id
        ];
        $response = $this->http->post($url, compact('headers', 'json'));
        return $this->format($response);
    }

    public function createCapture(string $paymentId, int $amount)
    {
        $url = "payments/$paymentId/captures";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $json = compact('amount');
        $response = $this->http->post($url, compact('headers', 'json'));
        return $this->format($response);
    }

    public function createCharge(string $paymentId, string $cvv, string $token)
    {
        $url = "payments/$paymentId/charges";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
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

    public function createPayment(
        int $amount,
        string $currency,
        object $additional_details = null,
        string $statement_soft_descriptor = null,
        object $order = null,
        string $customer_id = null,
        object $shipping_address = null,
        object $billing_address = null
    )
    {
        $url = 'payments';
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $json = array_filter(
            compact(
                'amount',
                'currency',
                'additional_details',
                'statement_soft_descriptor',
                'order',
                'customer_id',
                'shipping_address',
                'billing_address',
            ),
            'is_not_null',
        );
        $response = $this->http->post($url, compact('headers', 'json'));
        return $this->format($response);
    }

    public function makeRefund(string $paymentID) {
        $url = "https://api.paymentsos.com/payments/${paymentID}/refunds";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $response = $this->http->post($url, compact('headers'));
        return $this->format($response);
    }

    public function makeVoid(string $paymentID) {
        $url = "https://api.paymentsos.com/payments/${paymentID}/refunds";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $response = $this->http->post($url, compact('headers'));
        return $this->format($response);
    }

    public function createToken(
        string $card_number,
        string $credit_card_cvv,
        string $expiration_date,
        string $holder_name,
        string $token_type
    )
    {
        $url = 'tokens';
        $headers = array_merge($this->headers, [
            'public_key' => getenv('PAYU_PUBLIC_KEY'),
        ]);
        $json = compact(
            'card_number' ,
            'credit_card_cvv',
            'expiration_date',
            'holder_name',
            'token_type',
        );
        $response = $this->http->post($url, compact('headers', 'json'));
        return $this->format($response);
    }

    private function format(Response $response)
    {
        $guzzleBodyStream = $response->getBody();
        $json_string = (string) $guzzleBodyStream;
        $array = json_decode($json_string, true);
        return $array;
    }

    public function getAuthorization(string $paymentId, string $authorizationid)
    {
        $url = "payments/$paymentId/authorizations/$authorizationid";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $response = $this->http->get($url, compact('headers'));
        return $this->format($response);
    }
}
