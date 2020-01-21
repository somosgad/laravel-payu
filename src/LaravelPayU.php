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
            /* content type is auto-added by guzzle */
            // 'Content-Type' => 'application/json',
            'x-payments-os-env' => env('PAYU_ENV', 'test'),
        ];
        $this->app_id = env('PAYU_APP_ID');
        $this->private_key = env('PAYU_PRIVATE_KEY');
        $this->provider = env('PAYU_PROVIDER');
        $this->customer_device = config('laravel-payu.customer_device', false);
        $this->double_amounts = config('laravel-payu.double_amounts', false);
        $this->timeout = config('laravel-payu.timeout', 60);
        $this->zooz_request_id = config('laravel-payu.zooz_request_id', false);
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
     * Create authorization.
     * Create an authorization for a payment.
     *
     * recieve:
     *  string $payment_id identifier of the payment.
     *         ex: 9640e09b-85d0-4509-a19c-90aa65eb386a
     *  string $cvv the cvv value of the customer's card
     *  string $token token which represents the billing
     *  string $reconciliation_id (optional) A unique ID, used for reconciliation.
     *         -- Case the parameter is not passed it will be generated by
     *            the function _reconciliationId() which return a string with a number
     *            between 10000000 and 99999999
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
            'app-id' => $this->app_id,
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
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'json', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }


    /*
     * Create capture
     * --Important! You can capture a payment only after it is authorized.
     *
     * recieve:
     *  string $payment_id identifier of the payment.
     *         ex: 9640e09b-85d0-4509-a19c-90aa65eb386a
     *  int $amount is the amount of the minor unit of currency in transaction.
     *          -- $10.95 USD, amount will be 1095.
     *          -- ¥1095 JPY, amount will be 1095.
     *      --Important! For a full capture, the amount must be empty.
     *        For a partial capture the amount must be set.
     */
    public function createCapture(string $payment_id, int $amount)
    {
        $url = "payments/$payment_id/captures";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $json = compact('amount');
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'json', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Create charge
     * Create a new billing charge.
     *
     * recieve:
     *  string $payment_id identifier of the payment.
     *         ex: 9640e09b-85d0-4509-a19c-90aa65eb386a
     *  string $token token which represent the billing.
     *  string $credit_card_cvv the cvv number of client card.
     *  bool $cash_payment is one of:
     *          -- true if it's a cash payment
     *          -- false || null if it's not
     * @return array
     */
    public function createCharge(
        string $payment_id,
        string $token,
        string $credit_card_cvv = null,
        bool $cash_payment
    )
    {
        $url = "payments/$payment_id/charges";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
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
                            /* required, not listed at the docs but, apparently,
                             * it can also be set at the payu providers panel, not here */
                            // 'payment_country' => 'ARG',
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
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'json', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Create customer
     * Create a new Customer.
     *
     * recieve:
     *  string $customer_reference the unique customer identifier.
     *         -- Important! this has to be unique in whole system.
     *  string $email customer email address.
     * @return array
     */
    public function createCustomer(
        string $customer_reference,
        string $email
    )
    {
        $url = "customers";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $json = compact('customer_reference', 'email');
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'json', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Create a payment
     * Creates a payment object, which provides a single reference to all the
     * transactions that make up a payment.
     *
     * recieve:
     *  int $amount is the amount of the minor unit of currency in transaction.
     *          -- $10.95 USD, amount will be 1095.
     *          -- ¥1095 JPY, amount will be 1095.
     *  string $currency is the payment currency in ISO 4217 code.
     *          --'USD' is US Dollar
     *          --'ARS' is Argentinian Peso.
     *          --'EUR' is European Euro.
     *  bool $cash_payment is one of:
     *          -- true if it's a cash payment
     *          -- false || null if it's not
     *  object $additional_details is an object containing optional additional
     *        data stored in key/value pairs.
     *  string $statement_soft_descriptor The transaction description that will
     *         appear in the customer's credit card statement, which identifies
     *         the merchant and payment.
     *  object $order is an object containing the details of the order.
     *  string $customer_id value that identifies the client in the transaction.
     *  object $shipping_address object with shipping address details in key-value.
     *  object $billing_address object with billing addres details in key-value.
     * @return array
     */
    public function createPayment(
        int $amount,
        string $currency,
        bool $cash_payment = null,
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
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        if ($this->double_amounts) {
            $amount = $amount * 100;
        }

        $currency = strtoupper($currency);
        if($cash_payment){
            $json = array_filter(
                compact(
                    'amount',
                    'currency',
                    'statement_soft_descriptor',
                )
            );
        } else {
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
        }
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'json', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Create payment method
     * Associates a token with a customer.
     *
     * recieve:
     *  string $customer_id Identifier of the customer associated with this payment
     *  string $token Token that represents the customer
     * @return array
     */
    public function createPaymentMethod(
        string $customer_id,
        string $token
    )
    {
        $url = "customers/${customer_id}/payment-methods/${token}";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Create token
     * Create a new token for represent a customer.
     *
     * recieve:
     *  string $card_number is the customer card number.
     *  string $credit_card_cvv is the customer card cvv.
     *  string $expiration_date is the customer card expiration date.
     *         -- Possible formats:
     *               mm-yyyy, mm-yy, mm.yyyy, mm.yy,
     *               mm/yy, mm/yyyy, mm yyyy, or mm yy.
     *  string $holder_name the name of the card holder.
     *  string $token_type can be one of:
     *          -- "credit_card" which represents the credit card
     *          -- "card_cvv_code" which represents the card sensitive data encrypted
     *          -- "billing_agreement" which represents a billing
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
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'json', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Delete customer
     * Deletes a customer
     *
     * recieve:
     *  string $customer_id the customers identification
     * @return boolean
     */
    public function deleteCustomer(string $customer_id)
    {
        $url = "customers/{$customer_id}";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $timeout = $this->timeout;
        try {
            $response = $this->http->delete($url, compact('headers', 'timeout'));
            $status = $response->getStatusCode();
            return $status === 204 ? true : false;
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * To do.
     *
     */
    public function getAPIKeys() {
    }

    public function getAuthorization(string $payment_id, string $authorization_id)
    {
        $url = "payments/$payment_id/authorizations/$authorization_id";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $timeout = $this->timeout;
        try {
            $response = $this->http->get($url, compact('headers', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
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
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $timeout = $this->timeout;
        try {
            $response = $this->http->get($url, compact('headers', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     *  Get customer by reference
     *  Get the customer data by its reference.
     *
     * recieve:
     *  string $customer_reference the unique customer identifier.
     *         -- Important! this has to be unique in whole system.
     * @return array
     */
    public function getCustomerByReference(string $customer_reference)
    {
        $url = "customers";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $query = compact('customer_reference');
        $timeout = $this->timeout;
        try {
            $response = $this->http->get($url, compact('headers', 'query', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Get all supported payment methods.
     *
     * @return array
     */
    public function getSupportedPaymentMethods()
    {
        $url = "supported-payment-methods";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $timeout = $this->timeout;
        try {
            $response = $this->http->get($url, compact('headers', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * Get a token.
     *
     * @return array
     */
    public function getToken(string $token)
    {
        $url = "tokens/${token}";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $timeout = $this->timeout;
        try {
            $response = $this->http->get($url, compact('headers', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    public function makeRefund(string $payment_id) {
        $url = "payments/${payment_id}/refunds";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    public function makeVoid(string $payment_id) {
        $url = "payments/${payment_id}/voids";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return $this->_format($response, $e);
        }
    }

    /**
     * To do.
     *
     */
    public function replaceAPIKey() {
    }
}
