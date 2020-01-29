<?php

namespace SomosGAD_\LaravelPayU\Providers;

use Exception;
use SomosGAD_\LaravelPayU\Providers\PayULatam;
use SomosGAD_\LaravelPayU\RequestBodySchemas\PaymentMethods\UntokenizedAlternativePayment;
use SomosGAD_\LaravelPayU\RequestBodySchemas\BillingAddress;
use SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\Payment;
use SomosGAD_\LaravelPayU\RequestBodySchemas\ShippingAddress;

class PayUArgentina extends PayULatam
{
    private $currencies = ['ARS', 'USD'];

    public function createCashCharge(
        string $payment_id,
        UntokenizedAlternativePayment $payment_method,
        string $reconciliation_id
    ) {
        return $this->createGenericCharge(
            $payment_id,
            $payment_method,
            $reconciliation_id
        );
    }

    public function createCashPayment(
        int $amount,
        string $currency,
        string $statement_soft_descriptor,
        BillingAddress $billing_address = null,
        ShippingAddress $shipping_address = null
    ) {
        if ( ! in_array($currency, $this->currencies)) {
            throw new Exception("PayU Argentina doesn't support $currency currency.");
        }
        return $this->createGenericPayment(
            $amount,
            $currency,
            true,
            null,
            $statement_soft_descriptor,
            null,
            null,
            $shipping_address,
            $billing_address
        );
    }

    public function createPayment2(Payment $payment)
    {
        if ( ! in_array($payment->currency, $this->currencies)) {
            throw new Exception("PayU Argentina doesn't support $payment->currency currency.");
        }
        return parent::createPayment2($payment);
    }
}
