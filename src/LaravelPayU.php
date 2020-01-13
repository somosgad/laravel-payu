<?php

namespace SomosGAD_\LaravelPayU;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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

    private function _format(Response $response, RequestException $error = null)
    {
        $bodyStream = $response->getBody();
        $bodyString = $bodyStream->getContents();
        $data = json_decode($bodyString, true);
        if ($error) {
            $message = $error->getMessage();
            return [
                'error' => compact('message', 'data'),
            ];
        }
        return $data;
    }

    public function createAuthorization(
        string $payment_id,
        string $cvv,
        string $token,
        string $reconciliation_id = null
    )
    {
        $url = "payments/$payment_id/authorizations";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $payment_method = [
            'credit_card_cvv' => $cvv,
            'token' => $token,
            'type' => 'tokenized',
        ];
        $json = array_filter(
            compact(
                'payment_method',
                'reconciliation_id',
            ),
            'is_not_null',
        );
        try {
            $response = $this->http->post($url, compact('headers', 'json'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    public function createCapture(string $paymentId, int $amount)
    {
        $url = "payments/$paymentId/captures";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $json = compact('amount');
        try {
            $response = $this->http->post($url, compact('headers', 'json'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    public function createCharge(
        string $paymentId,
        string $token,
        string $credit_card_cvv = null
    )
    {
        $url = "payments/$paymentId/charges";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $type = 'tokenized';
        $json = [
            'payment_method' => array_filter(
                compact('credit_card_cvv', 'token', 'type'),
                'is_not_null',
            ),
        ];
        try {
            $response = $this->http->post($url, compact('headers', 'json'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Create customer
     *
     * @return array
     */
    public function createCustomer(
        string $customer_reference,
        string $email
    )
    {
        $url = "customers";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        $json = compact('customer_reference', 'email');
        try {
            $response = $this->http->post($url, compact('headers', 'json'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Create a payment
     *
     * @return array
     */
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
        $double_amounts = config('laravel-payu.double_amounts');
        if ($double_amounts) {
            $amount = $amount * 100;
        }
        $currency = strtoupper($currency);
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
        try {
            $response = $this->http->post($url, compact('headers', 'json'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Delete customer
     *
     * @return boolean
     */
    public function deleteCustomer(string $customer_id)
    {
        $url = "customers/{$customer_id}";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        try {
            $response = $this->http->delete($url, compact('headers'));
            $status = $response->getStatusCode();
            return $status === 204 ? true : false;
        } catch (RequestException $e) {
            return false;
        }
    }

    public function makeRefund(string $paymentID) {
        $url = "payments/${paymentID}/refunds";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        try {
            $response = $this->http->post($url, compact('headers'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    public function makeVoid(string $paymentID) {
        $url = "payments/${paymentID}/voids";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        try {
            $response = $this->http->post($url, compact('headers'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
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
        try {
            $response = $this->http->post($url, compact('headers', 'json'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    public function getAuthorization(string $paymentId, string $authorizationid)
    {
        $url = "payments/$paymentId/authorizations/$authorizationid";
        $headers = array_merge($this->headers, [
            'idempotency_key' => rand(),
            'private_key' => $this->private_key,
        ]);
        try {
            $response = $this->http->get($url, compact('headers'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }
}
