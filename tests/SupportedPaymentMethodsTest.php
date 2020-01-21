<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\LaravelPayU;

class SupportedPaymentMethodsTest extends TestCase
{
    /**
     * Test get supported payment methods.
     *
     * @depends SomosGAD_\LaravelPayU\Tests\InstanceTest::testInstance
     * @return void
     */
    public function testGetSupportedPaymentMethods(LaravelPayU $payu)
    {
        $paymentMethods = $payu->getSupportedPaymentMethods();

        $this->assertIsArray($paymentMethods);

        $this->assertCount(1, $paymentMethods);

        $this->assertArrayHasKey('configuration_id', $paymentMethods[0]);
        $this->assertArrayHasKey('configuration_name', $paymentMethods[0]);
        $this->assertArrayHasKey('provider_id', $paymentMethods[0]);
        $this->assertArrayHasKey('provider_name', $paymentMethods[0]);
        $this->assertArrayHasKey('supported_payment_methods', $paymentMethods[0]);
        $this->assertArrayHasKey('result', $paymentMethods[0]);

        $this->assertIsString($paymentMethods[0]['configuration_id']);
        $this->assertIsString($paymentMethods[0]['configuration_name']);
        $this->assertIsString($paymentMethods[0]['provider_id']);
        $this->assertIsString($paymentMethods[0]['provider_name']);
        $this->assertIsArray($paymentMethods[0]['supported_payment_methods']);
        $this->assertIsArray($paymentMethods[0]['result']);

        $this->assertArrayHasKey('status', $paymentMethods[0]['result']);
        $this->assertArrayHasKey('category', $paymentMethods[0]['result']);
        $this->assertArrayHasKey('description', $paymentMethods[0]['result']);

        $this->assertIsString($paymentMethods[0]['result']['status']);
        $this->assertIsString($paymentMethods[0]['result']['category']);
        $this->assertIsString($paymentMethods[0]['result']['description']);
    }
}
