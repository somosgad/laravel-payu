<?php

namespace SomosGAD_\LaravelPayU;

use SomosGAD_\LaravelPayU\RequestsSchemas\PaymentMethod\UntokenizedAlternativePayment;

class LaravelPayUArgentina extends LaravelPayUBase
{
    public function createCashCharge(
        string $payment_id,
        UntokenizedAlternativePayment $payment_method,
        string $reconciliation_id
    )
    {
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
    )
    {
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
}
