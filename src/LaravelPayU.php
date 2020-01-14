<?php

namespace SomosGAD_\LaravelPayU;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;

class LaravelPayU
{
    private $http;
    private $headers;
    private $private_key;
    private $provider;
    private $customer_device;
    private $zooz_request_id;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => 'https://api.paymentsos.com',
        ]);
        $this->headers = [
            'api-version' => '1.3.0',
            'app-id' => env('PAYU_APP_ID'),
            /* content type is auto-added by guzzle */
            // 'Content-Type' => 'application/json',
            'x-payments-os-env' => env('PAYU_ENV'),
        ];
        $this->private_key = env('PAYU_PRIVATE_KEY');
        $this->provider = env('PAYU_PROVIDER');
        $this->customer_device = config('laravel-payu.customer_device');
        $this->zooz_request_id = config('laravel-payu.zooz_request_id');
    }

    private function _format(Response $response, RequestException $error = null)
    {
        $bodyStream = $response->getBody();
        $bodyString = $bodyStream->getContents();
        $data = json_decode($bodyString, true);
        if ($this->zooz_request_id && $response->hasHeader('x-zooz-request-id')) {
            $data['x-zooz-request-id'] = $response->getHeader('x-zooz-request-id');
        }
        if ($error) {
            $message = $error->getMessage();
            return [
                'error' => compact('message', 'data'),
            ];
        }
        return $data;
    }

    /**
     * Generate a random string key to avoid duplicated requests.
     *
     * @return string
     */
    private function _idemPotencyKey() {
        return Str::random(25);
    }

    /**
     * A unique ID generated by you, used for transaction reconciliation.
     * Alphanumeric characters only. Maximum length: 255 characters.
     *
     * @return string
     */
    private function _reconciliationId() {
        return (string) rand(10000000, 99999999);
    }

    /**
     * Check if it needs to add the customer device information to the headers.
     *
     * @return array
     */
    private function _checkCustomerDevice($headers)
    {
        if ($this->customer_device) {
            $request = request();
            $headers = array_merge($headers, [
                'x-client-ip-address' => $request->ip(),
                'x-client-user-agent' => $request->header('User-Agent'),
            ]);
        }
        return $headers;
    }

    /**
     * Create customer.
     *
     * @return array
     */
    public function createAuthorization(
        string $payment_id,
        string $cvv,
        string $token,
        string $reconciliation_id = null
    )
    {
        $url = "payments/$payment_id/authorizations";
        $headers = array_merge($this->headers, [
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $headers = $this->_checkCustomerDevice($headers);
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
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
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

    /**
     * Create charge
     *
     * @return array
     */
    public function createCharge(
        string $paymentId,
        string $token,
        string $credit_card_cvv = null
    )
    {
        $url = "payments/$paymentId/charges";
        $headers = array_merge($this->headers, [
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $headers = $this->_checkCustomerDevice($headers);
        $type = 'tokenized';
        $json = [
            'payment_method' => array_filter(
                compact('credit_card_cvv', 'token', 'type'),
                'is_not_null',
            ),
        ];
        if ($this->provider === 'PayU Argentina') {

            // Send the session cookie stored on the device where the transaction was performed from.
            $cookie = '';

            // Only required if a customer is associated with the payment.
            $customer_national_identify_number = '123456';

            // The session identifier that you generate, of the device on which the transaction was performed.
            $device_fingerprint = '35';

            $json = array_merge($json, [
                'provider_specific_data' => [
                    'payu_latam' => [
                        'additional_details' => [
                            'cookie' => $cookie,
                            'customer_national_identify_number' => $customer_national_identify_number,
                            'payer_email' => 'John.Doe@email.com',
                            'payment_country' => 'ARG', // required not listed at docs
                        ],
                        'device_fingerprint' => [
                            'fingerprint' => $device_fingerprint,
                            'provider' => 'PayULatam',
                        ],
                    ],
                ],
                'reconciliation_id' => $this->_reconciliationId(),
            ]);
        }
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
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
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
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
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
     * Create payment method
     *
     * @return array
     */
    public function createPaymentMethod(
        string $customer_id,
        string $token
    )
    {
        $url = "customers/${customer_id}/payment-methods/${token}";
        $headers = array_merge($this->headers, [
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        try {
            $response = $this->http->post($url, compact('headers'));
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
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        try {
            $response = $this->http->delete($url, compact('headers'));
            $status = $response->getStatusCode();
            return $status === 204 ? true : false;
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Get customer by id
     *
     * @return array
     */
    public function getCustomerById(string $customer_id)
    {
        $url = "customers/{$customer_id}";
        $headers = array_merge($this->headers, [
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        try {
            $response = $this->http->get($url, compact('headers'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Get customer by reference
     *
     * @return array
     */
    public function getCustomerByReference(string $customer_reference)
    {
        $url = "customers";
        $headers = array_merge($this->headers, [
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $query = compact('customer_reference');
        try {
            $response = $this->http->get($url, compact('headers', 'query'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Get all supported payment methods
     *
     * @return array
     */
    public function getSupportedPaymentMethods()
    {
        $url = "supported-payment-methods";
        $headers = array_merge($this->headers, [
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        try {
            $response = $this->http->get($url, compact('headers'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    public function makeRefund(string $paymentID) {
        $url = "payments/${paymentID}/refunds";
        $headers = array_merge($this->headers, [
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
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
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        try {
            $response = $this->http->post($url, compact('headers'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Create token
     *
     * @return array
     */
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
            'public_key' => env('PAYU_PUBLIC_KEY'),
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
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
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
