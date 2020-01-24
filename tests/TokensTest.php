<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\LaravelPayU;

class TokensTest extends TestCase
{
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
     * Test create token.
     *
     * @dataProvider genericProvider
     * @dataProvider creditProvider
     * @dataProvider debitProvider
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return array
     */
    public function testCreateToken(
        string $card_number,
        string $credit_card_cvv,
        string $expiration_date,
        string $holder_name,
        string $token_type,
        LaravelPayU $payu
    )
    {
        $payu = new LaravelPayU;

        $token = $payu->createToken(
            $card_number,
            $credit_card_cvv,
            $expiration_date,
            $holder_name,
            $token_type
        );

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
     * Test get token.
     *
     * @dataProvider genericProvider
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return array
     */
    public function testGetToken(
        string $card_number,
        string $credit_card_cvv,
        string $expiration_date,
        string $holder_name,
        string $token_type,
        LaravelPayU $payu
    )
    {
        $payu = new LaravelPayU;

        $create_token = $payu->createToken(
            $card_number,
            $credit_card_cvv,
            $expiration_date,
            $holder_name,
            $token_type
        );

        $token = $payu->getToken($create_token['token']);

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
