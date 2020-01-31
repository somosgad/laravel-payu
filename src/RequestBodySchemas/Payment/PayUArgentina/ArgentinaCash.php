<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\PayUArgentina;

use SomosGAD_\LaravelPayU\RequestBodySchemas\BillingAddress;
use SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\Payment;
use SomosGAD_\LaravelPayU\RequestBodySchemas\ShippingAddress;

class ArgentinaCash extends Payment
{
    function __construct(
        int $amount,
        string $currency,
        string $statement_soft_descriptor,
        BillingAddress $billing_address = null,
        ShippingAddress $shipping_address = null,
        string $customer_id = null
    ) {
        parent::__construct($amount, $currency);
        $this->statement_soft_descriptor = $statement_soft_descriptor;
        $this->billing_address = $billing_address;
        $this->shipping_address = $shipping_address;
        $this->customer_id = $customer_id;
    }
}
