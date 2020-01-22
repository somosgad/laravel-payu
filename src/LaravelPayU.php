<?php

namespace SomosGAD_\LaravelPayU;

class LaravelPayU extends LaravelPayUBase
{
    public function createPayment(
        int $amount,
        string $currency,
        bool $cash_payment = null,
        object $additional_details = null,
        string $statement_soft_descriptor = null,
        object $order = null,
        string $customer_id = null,
        object $shipping_address = null,
        object $billing_address = null
    )
    {
        return $this->createGenericPayment(
            $amount,
            $currency,
            $cash_payment,
            $additional_details,
            $statement_soft_descriptor,
            $order,
            $customer_id,
            $shipping_address,
            $billing_address
        );
    }
}
