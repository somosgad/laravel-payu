<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\TestCase;
use SomosGAD_\LaravelPayU\LaravelPayU;

class LaravelPayUTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreatePayment()
    {
        $payment = LaravelPayU::createPayment();

        $this->assertArrayHasKey('id', $payment);
        $this->assertArrayHasKey('currency', $payment);
        $this->assertArrayHasKey('created', $payment);
        $this->assertArrayHasKey('modified', $payment);
        $this->assertArrayHasKey('status', $payment);
        $this->assertArrayHasKey('possible_next_actions', $payment);
        $this->assertArrayHasKey('amount', $payment);

        $this->assertIsString($payment['id']);
        $this->assertIsString($payment['currency']);
        $this->assertIsString($payment['created']);
        $this->assertIsString($payment['modified']);
        $this->assertIsString($payment['status']);
        $this->assertIsArray($payment['possible_next_actions']);
        $this->assertIsNumeric($payment['amount']);
    }
}
