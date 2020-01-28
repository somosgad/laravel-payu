<?php

namespace SomosGAD_\LaravelPayU;

use SomosGAD_\LaravelPayU\RequestsBodySchemas\PaymentMethods\Tokenized;

class LaravelPayU extends LaravelPayUBase
{
    public function createCharge(
        string $payment_id,
        string $token,
        string $credit_card_cvv = null,
        bool $cash_payment = null,
        string $cash_vendor = null,
        array $additional_details = []
    )
    {
        $payment_method = new Tokenized('tokenized', $token, $credit_card_cvv);
        return $this->createGenericCharge(
            $payment_id,
            $payment_method
        );
    }

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
