<?php

namespace SomosGAD_\LaravelPayU;

class LaravelPayUArgentina extends LaravelPayUBase
{
    public function createCashCharge(
        PaymentMethod $payment_method, string $reconciliation_id
    )
    {
        dd($payment_method, $reconciliation_id);
        /* return $this->createGenericCharge(
            $payment_method,
            $reconciliation_id
        ); */
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
