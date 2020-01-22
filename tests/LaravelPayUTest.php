<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\LaravelPayU;

class LaravelPayUTest extends TestCase
{
    /**
     * Mock create token.
     *
     * @return void
     */
    private function mockToken()
    {
        $payu = new LaravelPayU;
        $card_number = '4111111111111111';
        $credit_card_cvv = '123';
        $expiration_date = '10/29';
        $holder_name = 'John Doe';
        $token_type = 'credit_card';
        $token = $payu->createToken(
            $card_number,
            $credit_card_cvv,
            $expiration_date,
            $holder_name,
            $token_type
        );
        return $token;
    }

    /**
     * Test create authorization.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return void
     */
    public function testCreateAuthorization(LaravelPayU $payu)
    {
        $amount = 2000;
        $currency = 'USD';
        $payment = $payu->createPayment($amount, $currency);

        $token = $this->mockToken();
        $authorization = $payu->createAuthorization(
            $payment['id'],
            $token['encrypted_cvv'],
            $token['token']
        );

        $this->assertIsArray($authorization);

        $this->assertArrayHasKey('id', $authorization);
        $this->assertArrayHasKey('created', $authorization);
        $this->assertArrayHasKey('provider_specific_data', $authorization);
        $this->assertArrayHasKey('payment_method', $authorization);
        $this->assertArrayHasKey('result', $authorization);
        $this->assertArrayHasKey('provider_data', $authorization);
        $this->assertArrayHasKey('amount', $authorization);
        $this->assertArrayHasKey('provider_configuration', $authorization);

        $this->assertIsString($authorization['id']);
        $this->assertIsNumeric($authorization['created']);
        $this->assertIsArray($authorization['provider_specific_data']);
        $this->assertIsArray($authorization['payment_method']);
        $this->assertIsArray($authorization['result']);
        $this->assertIsArray($authorization['provider_data']);
        $this->assertIsNumeric($authorization['amount']);
        $this->assertIsArray($authorization['provider_configuration']);

        $this->assertEqualsIgnoringCase('Authorized.', $authorization['provider_data']['description']);

        return compact('payu', 'payment', 'authorization');
    }

    /**
     * Test create capture.
     *
     * @depends testCreateAuthorization
     * @return void
     */
    public function testCreateCapture(array $data)
    {
        $capture = $data['payu']->createCapture(
            $data['payment']['id'],
            $data['payment']['amount'],
        );

        $this->assertIsArray($capture);

        $this->assertArrayHasKey('id', $capture);
        $this->assertArrayHasKey('created', $capture);
        $this->assertArrayHasKey('result', $capture);
        $this->assertArrayHasKey('amount', $capture);
        $this->assertArrayHasKey('provider_data', $capture);
        $this->assertArrayHasKey('provider_configuration', $capture);

        $this->assertIsString($capture['id']);
        $this->assertIsNumeric($capture['created']);
        $this->assertIsArray($capture['result']);
        $this->assertIsNumeric($capture['amount']);
        $this->assertIsArray($capture['provider_data']);
        $this->assertIsArray($capture['provider_configuration']);
    }

    /**
     * Test create charge.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @depends testCreatePayment
     * @return void
     */
    public function testCreateCharge(LaravelPayU $payu, array $payment)
    {
        $token = $this->mockToken();
        $charge = $payu->createCharge(
            $payment['id'],
            $token['token']
            // $token['encrypted_cvv'],
        );

        $this->assertIsArray($charge);

        $this->assertArrayHasKey('id', $charge);
        $this->assertArrayHasKey('created', $charge);
        $this->assertArrayHasKey('provider_specific_data', $charge);
        $this->assertArrayHasKey('payment_method', $charge);
        $this->assertArrayHasKey('result', $charge);
        $this->assertArrayHasKey('provider_data', $charge);
        $this->assertArrayHasKey('amount', $charge);
        $this->assertArrayHasKey('provider_configuration', $charge);

        $this->assertIsString($charge['id']);
        $this->assertIsNumeric($charge['created']);
        $this->assertIsArray($charge['provider_specific_data']);
        $this->assertIsArray($charge['payment_method']);
        $this->assertIsArray($charge['result']);
        $this->assertIsArray($charge['provider_data']);
        $this->assertIsNumeric($charge['amount']);
        $this->assertIsArray($charge['provider_configuration']);

        $this->assertEqualsIgnoringCase('Captured.', $charge['provider_data']['description']);
    }

    /**
     * Test create customer
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return array
     */
    public function testCreateCustomer(LaravelPayU $payu)
    {
        $customer_reference = 'johntravolta18021954';
        $email = 'john@travolta.com';
        $customer = $payu->createCustomer(
            $customer_reference,
            $email
        );

        $this->assertIsArray($customer);

        $this->assertArrayHasKey('id', $customer);
        $this->assertArrayHasKey('created', $customer);
        $this->assertArrayHasKey('modified', $customer);
        $this->assertArrayHasKey('customer_reference', $customer);
        $this->assertArrayHasKey('email', $customer);

        $this->assertIsString($customer['id']);
        $this->assertIsNumeric($customer['created']);
        $this->assertIsNumeric($customer['modified']);
        $this->assertIsString($customer['customer_reference']);
        $this->assertIsString($customer['email']);

        return $customer;
    }

    /**
     * Test delete customer
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @depends testGetCustomerById
     * @depends testGetCustomerByReference
     * @depends testCreatePaymentMethod
     * @return void
     */
    public function testDeleteCustomer(
        LaravelPayU $payu,
        array $customer,
        array $customer_by_reference,
        array $payment_method
    )
    {
        $delete = $payu->deleteCustomer($customer['id']);

        $this->assertIsBool($delete);
    }

     /**
     * Test Void. a void cancels an operation (such as an authorization or
     * capture), before it has been finalized. The most common procedure is
     * to void an authorization.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @depends testCreatePayment
     * @return void
     */
    public function testMakeVoid(LaravelPayU $payu, array $payment)
    {
        $token = $this->mockToken();
        $charge = $payu->createCharge(
            $payment['id'],
            $token['token'],
            $token['encrypted_cvv']
        );

        $output = $payu->makeVoid($payment['id']);

        $this->assertIsArray($output);

        $this->assertArrayHasKey('id', $output);
        $this->assertArrayHasKey('created', $output);
        $this->assertArrayHasKey('provider_data', $output);
        $this->assertArrayHasKey('provider_specific_data', $output);
        $this->assertArrayHasKey('provider_configuration', $output);
    }

    /**
     * Test create payment.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return void
     */
    public function testMakeRefund(LaravelPayU $payu)
    {
        $amount = 1203.30;
        $currency = 'USD';
        $payment = $payu->createPayment($amount, $currency);

        $token = $this->mockToken();
        $charge = $payu->createCharge(
            $payment['id'],
            $token['token'],
            $token['encrypted_cvv']
        );

        $output = $payu->makeRefund($payment['id']);

        $this->assertIsArray($output);

        $this->assertArrayHasKey('id', $output);
        $this->assertArrayHasKey('created', $output);
        $this->assertArrayHasKey('provider_data', $output);
        $this->assertArrayHasKey('result', $output);
        $this->assertArrayHasKey('amount', $output);
        $this->assertArrayHasKey('provider_configuration', $output);
    }

    /**
     * Test create payment.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @depends testGetCustomerById
     * @return void
     */
    public function testCreatePaymentMethod(LaravelPayU $payu, array $customer)
    {
        $token = $this->mockToken();
        $payment_method = $payu->createPaymentMethod($customer['id'], $token['token']);

        $this->assertIsArray($payment_method);

        $this->assertArrayHasKey('type', $payment_method);
        $this->assertArrayHasKey('token', $payment_method);
        $this->assertArrayHasKey('token_type', $payment_method);
        $this->assertArrayHasKey('fingerprint', $payment_method);
        $this->assertArrayHasKey('state', $payment_method);
        $this->assertArrayHasKey('holder_name', $payment_method);
        $this->assertArrayHasKey('expiration_date', $payment_method);
        $this->assertArrayHasKey('last_4_digits', $payment_method);
        $this->assertArrayHasKey('pass_luhn_validation', $payment_method);
        $this->assertArrayHasKey('vendor', $payment_method);
        $this->assertArrayHasKey('created', $payment_method);
        $this->assertArrayHasKey('bin_number', $payment_method);
        $this->assertArrayHasKey('issuer', $payment_method);
        $this->assertArrayHasKey('card_type', $payment_method);
        $this->assertArrayHasKey('level', $payment_method);
        $this->assertArrayHasKey('country_code', $payment_method);
        $this->assertArrayHasKey('href', $payment_method);
        $this->assertArrayHasKey('customer', $payment_method);

        $this->assertIsString($payment_method['type']);
        $this->assertIsString($payment_method['token']);
        $this->assertIsString($payment_method['token_type']);
        $this->assertIsString($payment_method['fingerprint']);
        $this->assertIsString($payment_method['state']);
        $this->assertIsString($payment_method['holder_name']);
        $this->assertIsString($payment_method['expiration_date']);
        $this->assertIsNumeric($payment_method['last_4_digits']);
        $this->assertIsBool($payment_method['pass_luhn_validation']);
        $this->assertIsString($payment_method['vendor']);
        $this->assertIsNumeric($payment_method['created']);
        $this->assertIsNumeric($payment_method['bin_number']);
        $this->assertIsString($payment_method['issuer']);
        $this->assertIsString($payment_method['card_type']);
        $this->assertIsString($payment_method['level']);
        $this->assertIsString($payment_method['country_code']);
        $this->assertIsString($payment_method['href']);
        $this->assertIsString($payment_method['customer']);

        return $payment_method;
    }

    /**
     * Test create token.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return array
     */
    public function testCreateToken(LaravelPayU $payu)
    {
        $token = $this->mockToken();

        $this->assertIsArray($token);

        $this->assertArrayHasKey('token', $token);
        $this->assertArrayHasKey('created', $token);
        $this->assertArrayHasKey('pass_luhn_validation', $token);
        $this->assertArrayHasKey('encrypted_cvv', $token);
        $this->assertArrayHasKey('token_type', $token);
        $this->assertArrayHasKey('type', $token);
        $this->assertArrayHasKey('state', $token);
        $this->assertArrayHasKey('bin_number', $token);
        $this->assertArrayHasKey('vendor', $token);
        $this->assertArrayHasKey('card_type', $token);
        $this->assertArrayHasKey('issuer', $token);
        $this->assertArrayHasKey('level', $token);
        $this->assertArrayHasKey('country_code', $token);
        $this->assertArrayHasKey('holder_name', $token);
        $this->assertArrayHasKey('expiration_date', $token);
        $this->assertArrayHasKey('last_4_digits', $token);

        $this->assertIsString($token['token']);
        $this->assertIsNumeric($token['created']);
        $this->assertIsBool($token['pass_luhn_validation']);
        $this->assertIsString($token['encrypted_cvv']);
        $this->assertIsString($token['token_type']);
        $this->assertIsString($token['type']);
        $this->assertIsString($token['state']);
        $this->assertIsNumeric($token['bin_number']);
        $this->assertIsString($token['vendor']);
        $this->assertIsString($token['card_type']);
        $this->assertIsString($token['issuer']);
        $this->assertIsString($token['country_code']);
        $this->assertIsString($token['holder_name']);
        $this->assertIsString($token['expiration_date']);
        $this->assertIsNumeric($token['last_4_digits']);

        return $token;
    }

    /**
     * Test create authorization.
     *
     * @depends testCreateAuthorization
     * @return void
     */
    public function testGetAuthorization(array $data)
    {
        $authorization = $data['payu']->getAuthorization(
            $data['payment']['id'],
            $data['authorization']['id']
        );

        $this->assertIsArray($authorization);

        $this->assertArrayHasKey('id', $authorization);
        $this->assertArrayHasKey('created', $authorization);
        $this->assertArrayHasKey('payment_method', $authorization);
        $this->assertArrayHasKey('result', $authorization);
        $this->assertArrayHasKey('provider_data', $authorization);
        $this->assertArrayHasKey('amount', $authorization);
        $this->assertArrayHasKey('provider_configuration', $authorization);

        $this->assertIsString($authorization['id']);
        $this->assertIsNumeric($authorization['created']);
        $this->assertIsArray($authorization['payment_method']);
        $this->assertIsArray($authorization['result']);
        $this->assertIsArray($authorization['provider_data']);
        $this->assertIsNumeric($authorization['amount']);
        $this->assertIsArray($authorization['provider_configuration']);

        $this->assertEqualsIgnoringCase('Authorized.', $authorization['provider_data']['description']);
    }

    /**
     * Test get customer.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @depends testCreateCustomer
     * @return void
     */
    public function testGetCustomerById(LaravelPayU $payu, array $customer)
    {
        $customer = $payu->getCustomerById($customer['id']);

        $this->assertIsArray($customer);

        $this->assertArrayHasKey('id', $customer);
        $this->assertArrayHasKey('created', $customer);
        $this->assertArrayHasKey('modified', $customer);
        $this->assertArrayHasKey('customer_reference', $customer);
        $this->assertArrayHasKey('email', $customer);

        $this->assertIsString($customer['id']);
        $this->assertIsNumeric($customer['created']);
        $this->assertIsNumeric($customer['modified']);
        $this->assertIsString($customer['customer_reference']);
        $this->assertIsString($customer['email']);

        return $customer;
    }

    /**
     * Test get customer.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @depends testGetCustomerById
     * @return void
     */
    public function testGetCustomerByReference(LaravelPayU $payu, array $customer)
    {
        $customers = $payu->getCustomerByReference($customer['customer_reference']);

        $this->assertIsArray($customers);

        $this->assertCount(1, $customers);

        $this->assertIsArray($customers[0]);

        $this->assertArrayHasKey('id', $customers[0]);
        $this->assertArrayHasKey('created', $customers[0]);
        $this->assertArrayHasKey('modified', $customers[0]);
        $this->assertArrayHasKey('customer_reference', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);

        $this->assertIsString($customers[0]['id']);
        $this->assertIsNumeric($customers[0]['created']);
        $this->assertIsNumeric($customers[0]['modified']);
        $this->assertIsString($customers[0]['customer_reference']);
        $this->assertIsString($customers[0]['email']);

        return $customers[0];
    }

    /**
     * Test get token.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @depends testCreateToken
     * @return array
     */
    public function testGetToken(LaravelPayU $payu, array $token)
    {
        $token = $payu->getToken($token['token']);

        $this->assertIsArray($token);

        $this->assertArrayHasKey('token', $token);
        $this->assertArrayHasKey('created', $token);
        $this->assertArrayHasKey('pass_luhn_validation', $token);
        // $this->assertArrayHasKey('encrypted_cvv', $token);
        $this->assertArrayHasKey('token_type', $token);
        $this->assertArrayHasKey('type', $token);
        $this->assertArrayHasKey('state', $token);
        $this->assertArrayHasKey('bin_number', $token);
        $this->assertArrayHasKey('vendor', $token);
        $this->assertArrayHasKey('card_type', $token);
        $this->assertArrayHasKey('issuer', $token);
        $this->assertArrayHasKey('level', $token);
        $this->assertArrayHasKey('country_code', $token);
        $this->assertArrayHasKey('holder_name', $token);
        $this->assertArrayHasKey('expiration_date', $token);
        $this->assertArrayHasKey('last_4_digits', $token);

        $this->assertIsString($token['token']);
        $this->assertIsNumeric($token['created']);
        $this->assertIsBool($token['pass_luhn_validation']);
        // $this->assertIsString($token['encrypted_cvv']);
        $this->assertIsString($token['token_type']);
        $this->assertIsString($token['type']);
        $this->assertIsString($token['state']);
        $this->assertIsNumeric($token['bin_number']);
        $this->assertIsString($token['vendor']);
        $this->assertIsString($token['card_type']);
        $this->assertIsString($token['issuer']);
        $this->assertIsString($token['country_code']);
        $this->assertIsString($token['holder_name']);
        $this->assertIsString($token['expiration_date']);
        $this->assertIsNumeric($token['last_4_digits']);
    }
}
