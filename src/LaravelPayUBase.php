<?php

namespace SomosGAD_\LaravelPayU;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use SomosGAD_\LaravelPayU\RequestBodySchemas\BillingAddress;
use SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\Payment;
use SomosGAD_\LaravelPayU\RequestBodySchemas\PaymentMethods\PaymentMethod;
use SomosGAD_\LaravelPayU\RequestBodySchemas\ShippingAddress;
use SomosGAD_\LaravelPayU\RequestBodySchemas\Token\Token;
use Webmozart\Assert\Assert;

class LaravelPayUBase
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
     *  bool $cash_payment is one of:
     *          -- true if it's a cash payment
     *          -- false || null if it's not
     *  string $cash_vendor is the code of the cash vendor provided by PayU.
     *         ex: Argentina - "RAPIPAGO","COBRO_EXPRESS", Brasil - "BOLETO_BANCARIO",
     *             Chile - "MULTICAJA"
     *  array $additional_details object which contains the additional information
     *          necessary for the cash order creation. Usually contains:
     *          [-- string order_language => is the ISO 639-1 code of the text lang.
     *                     ex: -- "es" for Spanish
     *                         -- "en" for English
     *                         -- "pt" for Portuguese
     *           -- string cash_payment_method_vendor => is the same code as $cash_vendor
     *           -- string payment_method => is "PSE"
     *           -- string payment_country => is the ISO 3166-1 alpha-3 of the country]
     *           --Important! Each country has different keys, for know which is necessary
     *             read the PayU PaymentOS docs: https://developers.paymentsos.com/docs/
     * @return array
     */
    public function createGenericCharge(
        string $payment_id,
        PaymentMethod $payment_method,
        string $reconciliation_id = null
    )
    {
        $url = "payments/$payment_id/charges";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $headers = $this->_checkCustomerDevice($headers);
        if ( ! $reconciliation_id) {
            $reconciliation_id = $this->_reconciliationId();
        }
        $json = [
            'payment_method' => array_filter(
                (array) $payment_method,
                'is_not_null',
            ),
            'reconciliation_id' => $reconciliation_id,
        ];
        if ($this->provider === 'PayU Argentina' && false) {

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

    private function _validateCreateCharge(string $payment_id, array $json)
    {
        // payment id
        Assert::uuid($payment_id);
        // reconciliation id
        if (array_key_exists('reconciliation_id', $json)) {
            Assert::string($json['reconciliation_id']);
        }
        // payment method
        Assert::keyExists($json, 'payment_method');
        $payment_method = $json['payment_method'];
        // type
        Assert::keyExists($payment_method, 'type');
        Assert::string($payment_method['type']);
        Assert::oneOf($payment_method['type'], ['tokenized', 'untokenized']);

        // tokenized
        if ($payment_method['type'] === 'tokenized') {
            // token
            Assert::keyExists($payment_method, 'token');
            Assert::string($payment_method['token']);
            // credit card cvv
            if (array_key_exists('credit_card_cvv', $payment_method)) {
                Assert::string($payment_method['credit_card_cvv']);
            }

        // untokenized_alternative_payment
        } else if ($payment_method['type'] === 'untokenized') {
            // source type
            Assert::keyExists($payment_method, 'source_type');
            Assert::string($payment_method['source_type']);
            Assert::oneOf($payment_method['source_type'], [
                'bank_transfer', 'cash', 'ewallet', 'debit_redirect', 'loyalty'
            ]);
            // vendor
            if (array_key_exists('vendor', $payment_method)) {
                Assert::string($payment_method['vendor']);
                if ($this->provider === 'PayU Argentina') {
                    Assert::oneOf($payment_method['vendor'], ['COBRO_EXPRESS', 'PAGOFACIL', 'RAPIPAGO']);
                } else if ($this->provider === 'PayU Chile') {
                    Assert::same($payment_method['vendor'], 'MULTICAJA');
                }
            }
            // additional details
            if (array_key_exists('additional_details', $payment_method)) {
                Assert::isArray($payment_method['additional_details']);
            }
            // validate argentina cash type
            if ($payment_method['source_type'] === 'cash' && $this->provider === 'PayU Argentina') {
                $this->_validateArgentinaCashCharge($json);
            }

        // untokenized_credit_card
        } else {
            // source type
            Assert::keyExists($payment_method, 'source_type');
            Assert::string($payment_method['source_type']);
            Assert::same($payment_method['source_type'], 'credit_card');
            // holder_name
            Assert::keyExists($payment_method, 'holder_name');
            Assert::string($payment_method['holder_name']);
            // expiration date
            if (array_key_exists('expiration_date', $payment_method)) {
                Assert::string($payment_method['expiration_date']);
                Assert::regex($payment_method['expiration_date'], '^(0?[1-9]|1[0-2])(\/|\-|\.| )\d{2,4}$');
            }
            // card identity
            if (array_key_exists('card_identity', $payment_method)) {
                Assert::isArray($payment_method['card_identity']);
            }
            // credit card cvv
            if (array_key_exists('credit_card_cvv', $payment_method)) {
                Assert::string($payment_method['credit_card_cvv']);
                Assert::regex($payment_method['credit_card_cvv'], '^[0-9]{3}[0-9]?$');
            }
            // card number
            Assert::keyExists($payment_method, 'card_number');
            Assert::regex($payment_method['card_number'], '\d{8}|\d{12,19}');
        }

        // three_d_secure_attributes
        if (array_key_exists('three_d_secure_attributes', $json)) {
            Assert::isArray($json['three_d_secure_attributes']);
        }
        // installments
        if (array_key_exists('installments', $json)) {
            Assert::isArray($json['installments']);
        }
        // merchant_site_url
        if (array_key_exists('merchant_site_url', $json)) {
            Assert::string($json['merchant_site_url']);
            Assert::regex($json['merchant_site_url'], '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$');
        }
        // provider_specific_data
        if (array_key_exists('provider_specific_data', $json)) {
            Assert::isArray($json['provider_specific_data']);
        }
        // additional_details
        if (array_key_exists('additional_details', $json)) {
            Assert::isArray($json['additional_details']);
        }
        // cof_transaction_indicators
        if (array_key_exists('cof_transaction_indicators', $json)) {
            Assert::isArray($json['cof_transaction_indicators']);
        }
        // channel_type
        if (array_key_exists('channel_type', $json)) {
            Assert::string($json['channel_type']);
            Assert::oneOf($json['channel_type'], ['telephone_order', 'mail_order', 'virtual_terminal']);
        }
    }

    private function _validateArgentinaCashCharge(array $json)
    {
        // payment method
        Assert::keyExists($json, 'payment_method');
        if ($payment_method = $json['payment_method']) {
            // source type
            Assert::keyExists($payment_method, 'source_type');
            // type
            Assert::keyExists($payment_method, 'type');
            // vendor
            Assert::keyExists($payment_method, 'vendor');
            Assert::oneOf($payment_method['vendor'], ['COBRO_EXPRESS', 'PAGOFACIL', 'RAPIPAGO']);
            // additional details
            Assert::keyExists($payment_method, 'additional_details');
            if ($additional_details = $payment_method['additional_details']) {
                // order language
                Assert::keyExists($additional_details, 'order_language');
                Assert::length($additional_details['order_language'], 2);
                // cash_payment_method_vendor
                Assert::keyExists($additional_details, 'cash_payment_method_vendor');
                // payment_method
                Assert::keyExists($additional_details, 'payment_method');
                Assert::same($additional_details['payment_method'], 'PSE');
                // payment_country
                Assert::keyExists($additional_details, 'payment_country');
                Assert::length($additional_details['payment_country'], 3);
            }
        }
        // reconciliation id
        Assert::keyExists($json, 'reconciliation_id');
        Assert::maxLength($json['reconciliation_id'], 255);
    }

    public function createCharge2(string $payment_id, array $json)
    {
        $this->_validateCreateCharge($payment_id, $json);

        $url = "payments/$payment_id/charges";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        $headers = $this->_checkCustomerDevice($headers);
        if ( ! array_key_exists('reconciliation_id', $json)) {
            $json['reconciliation_id'] = $this->_reconciliationId();
        }
        $timeout = $this->timeout;
        try {
            $response = $this->http->post($url, compact('headers', 'json', 'timeout'));
            return $this->_format($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            dd($headers, $json);
            return $this->_format($response, $e);
        }
    }

    private function _validateCreateCustomer(array $json)
    {
        // customer reference
        Assert::keyExists($json, 'customer_reference');
        Assert::string($json['customer_reference']);
        // first_name
        if (array_key_exists('first_name', $json)) {
            Assert::string($json['first_name']);
        }
        // last_name
        if (array_key_exists('last_name', $json)) {
            Assert::string($json['last_name']);
        }
        // email
        if (array_key_exists('email', $json)) {
            Assert::string($json['email']);
        }
        // additional_details
        if (array_key_exists('additional_details', $json)) {
            Assert::isArray($json['additional_details']);
            foreach ($json['additional_details'] as $property) {
                Assert::string($property);
            }
        }
        // shipping_address
        if (array_key_exists('shipping_address', $json)) {
            $shipping_address = $json['shipping_address'];
            Assert::isArray($shipping_address);
            // country
            if (array_key_exists('country', $shipping_address)) {
                Assert::string($shipping_address['country']);
                Assert::regex($shipping_address['country'], '^[A-Z]{3}$');
            }
            // state
            if (array_key_exists('state', $shipping_address)) {
                Assert::string($shipping_address['state']);
            }
            // line1
            if (array_key_exists('line1', $shipping_address)) {
                Assert::string($shipping_address['line1']);
            }
            // line2
            if (array_key_exists('line2', $shipping_address)) {
                Assert::string($shipping_address['line2']);
            }
            // zip_code
            if (array_key_exists('zip_code', $shipping_address)) {
                Assert::string($shipping_address['zip_code']);
            }
            // title
            if (array_key_exists('title', $shipping_address)) {
                Assert::string($shipping_address['title']);
            }
            // first_name
            if (array_key_exists('first_name', $shipping_address)) {
                Assert::string($shipping_address['first_name']);
            }
            // last_name
            if (array_key_exists('last_name', $shipping_address)) {
                Assert::string($shipping_address['last_name']);
            }
            // phone
            if (array_key_exists('phone', $shipping_address)) {
                Assert::string($shipping_address['phone']);
            }
            // email
            if (array_key_exists('email', $shipping_address)) {
                Assert::string($shipping_address['email']);
            }
        }
        // payment_methods
        if (array_key_exists('payment_methods', $json)) {
            $payment_methods = $json['payment_methods'];
            Assert::isArray($payment_methods);
            Assert::maxCount($payment_methods, 10);
            foreach ($payment_methods as $property) {
                Assert::string($property);
            }
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
    public function createCustomer(array $json)
    {
        $this->_validateCreateCustomer($json);
        $url = "customers";
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
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
    protected function createGenericPayment(
        int $amount,
        string $currency,
        bool $cash_payment = null,
        object $additional_details = null,
        string $statement_soft_descriptor = null,
        object $order = null,
        string $customer_id = null,
        ShippingAddress $shipping_address = null,
        BillingAddress $billing_address = null
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
        if ($cash_payment){
            $json = array_filter(
                compact(
                    'amount',
                    'currency',
                    'statement_soft_descriptor',
                    'shipping_address',
                    'billing_address',
                ),
                'is_not_null',
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

    public function createPayment2(Payment $payment)
    {
        $url = 'payments';
        $headers = array_merge($this->headers, [
            'app-id' => $this->app_id,
            'idempotency-key' => $this->_idemPotencyKey(),
            'private-key' => $this->private_key,
        ]);
        if ($this->double_amounts) {
            $payment->amount = $payment->amount * 100;
        }
        $payment->currency = strtoupper($payment->currency);
        $json = array_filter($payment->toArray(), 'is_not_null');
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

    public function createToken2(Token $token)
    {
        $url = 'tokens';
        $headers = array_merge($this->headers, [
            'public_key' => env('PAYU_PUBLIC_KEY'),
        ]);
        $json = array_filter($token->toArray(), 'is_not_null');
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
