<?php

namespace SomosGAD_\LaravelPayU\Providers\PayUArgentina;

use SomosGAD_\LaravelPayU\RequestsBodySchemas\PaymentMethods\UntokenizedAlternativePayment;
use SomosGAD_\LaravelPayU\RequestsBodySchemas\BillingAddress;
use SomosGAD_\LaravelPayU\RequestsBodySchemas\ShippingAddress;
use SomosGAD_\LaravelPayU\PaymentMethodTypes\Cash;
use SomosGAD_\LaravelPayU\PaymentMethodTypes\PaymentMethodType;
use SomosGAD_\LaravelPayU\Providers\PayULatam;

class PayUArgentina extends PayULatam
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

    /*
     * @param Cards|Cash $type
     */
    public function createPayment(PaymentMethodType $type)
    {
        // if ($type instanceof Cash) {
            // return $this->createGenericPayment($type);
        // } else {
            /* return $this->createGenericPayment(
                $amount,
                $currency,
                true,
                null,
                $statement_soft_descriptor,
                null,
                null,
                $shipping_address,
                $billing_address
            ); */
        // }
    }
}
