<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\LaravelPayU;
use SomosGAD_\LaravelPayU\RequestsBodySchemas\PaymentMethods\Tokenized;

class ChargesTest extends TestCase
{
    public function approvedProvider()
    {
        $data = [
            'approved' => tokenData('376414000000009', '123', '10/29', 'APPROVED', 'credit_card'),
        ];
        return $data;
    }

    public function pendingProvider()
    {
        $data = [
            'pending' => tokenData('376414000000009', '123', '10/29', 'PENDING_TRANSACTION_REVIEW', 'credit_card'),
        ];
        return $data;
    }

    public function rejectedProvider()
    {
        $data = [
            'rejected' => tokenData('376414000000009', '123', '10/29', 'REJECTED', 'credit_card'),
        ];
        return $data;
    }

    public function creditProvider()
    {
        $data = [
            'amex credit' => tokenData('376414000000009', '123', '10/29', 'John Doe', 'credit_card'),
            'argencard credit' => tokenData('5011050000000001', '123', '10/29', 'John Doe', 'credit_card'),
            'cabal credit' => tokenData('5896570000000008', '123', '10/29', 'John Doe', 'credit_card'),
            'cencosud credit' => tokenData('6034930000000005', '123', '10/29', 'John Doe', 'credit_card'),
            'cencosud credit' => tokenData('5197670000000002', '123', '10/29', 'John Doe', 'credit_card'),
            'master credit' => tokenData('5399090000000009', '123', '10/29', 'John Doe', 'credit_card'),
            'naranja credit' => tokenData('5895620000000002', '123', '10/29', 'John Doe', 'credit_card'),
            'visa credit' => tokenData('4850110000000000', '123', '10/29', 'John Doe', 'credit_card'),
            'visa credit' => tokenData('4036820000000001', '123', '10/29', 'John Doe', 'credit_card'),
        ];
        return $data;
    }

    public function debitProvider()
    {
        $data = [
            'visa debit' => tokenData('4517730000000000', '123', '10/29', 'John Doe', 'credit_card'),
        ];
        return $data;
    }

    public function genericProvider()
    {
        $data = [
            'generic' => tokenData('4111111111111111', '123', '10/29', 'John Doe', 'credit_card'),
        ];
        return $data;
    }

    /**
     * Test create charge.
     *
     * @dataProvider approvedProvider
     * @dataProvider creditProvider
     * @dataProvider debitProvider
     * @dataProvider genericProvider
     * @dataProvider pendingProvider
     * @dataProvider rejectedProvider
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return void
     */
    public function testCreateCharge(
        string $card_number,
        string $credit_card_cvv,
        string $expiration_date,
        string $holder_name,
        string $token_type,
        LaravelPayU $payu
    )
    {
        $token = $payu->createToken(
            $card_number,
            $credit_card_cvv,
            $expiration_date,
            $holder_name,
            $token_type
        );

        $amount = 2000;
        $currency = 'USD';
        $payment = $payu->createPayment($amount, $currency);

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
