<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\LaravelPayU;

class PaymentsTest extends TestCase
{
    /**
     * Test create cash payment.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return array
     */
    public function testCreateCashPayment(LaravelPayU $payu)
    {
        $amount = 2000;
        $currency = 'USD';
        $payment = $payu->createPayment(
            $amount,
            $currency,
            true,
            null,
            "Payment test for www.somosgad.com"
        );

        $this->assertIsArray($payment);

        $this->assertArrayHasKey('id', $payment);
        $this->assertArrayHasKey('currency', $payment);
        $this->assertArrayHasKey('created', $payment);
        $this->assertArrayHasKey('modified', $payment);
        $this->assertArrayHasKey('status', $payment);
        $this->assertArrayHasKey('possible_next_actions', $payment);
        $this->assertArrayHasKey('amount', $payment);

        $this->assertIsString($payment['id']);
        $this->assertIsString($payment['currency']);
        $this->assertIsNumeric($payment['created']);
        $this->assertIsNumeric($payment['modified']);
        $this->assertIsString($payment['status']);
        $this->assertIsArray($payment['possible_next_actions']);
        $this->assertIsNumeric($payment['amount']);

        return $payment;
    }

    /**
     * Test create payment.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return array
     */
    public function testCreatePayment(LaravelPayU $payu)
    {
        $amount = 2000;
        $currency = 'USD';
        $payment = $payu->createPayment($amount, $currency);

        $this->assertIsArray($payment);

        $this->assertArrayHasKey('id', $payment);
        $this->assertArrayHasKey('currency', $payment);
        $this->assertArrayHasKey('created', $payment);
        $this->assertArrayHasKey('modified', $payment);
        $this->assertArrayHasKey('status', $payment);
        $this->assertArrayHasKey('possible_next_actions', $payment);
        $this->assertArrayHasKey('amount', $payment);

        $this->assertIsString($payment['id']);
        $this->assertIsString($payment['currency']);
        $this->assertIsNumeric($payment['created']);
        $this->assertIsNumeric($payment['modified']);
        $this->assertIsString($payment['status']);
        $this->assertIsArray($payment['possible_next_actions']);
        $this->assertIsNumeric($payment['amount']);

        return $payment;
    }
}
