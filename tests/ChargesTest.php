<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\LaravelPayU;
use SomosGAD_\LaravelPayU\RequestsSchemas\PaymentMethod\Tokenized;

class ChargesTest extends TestCase
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
     * Test create charge.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @depends SomosGAD_\LaravelPayU\Tests\PaymentsTest::testCreatePayment
     * @return void
     */
    public function testCreateCharge(LaravelPayU $payu, array $payment)
    {
        $token = $this->mockToken();
        // $payment_method = new Tokenized('tokenized', $token['token']);
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
}
