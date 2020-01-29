<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\PayUArgentina;

use SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\Payment;
use SomosGAD_\LaravelPayU\RequestBodySchemas\BillingAddress;
use SomosGAD_\LaravelPayU\RequestBodySchemas\Order;
use SomosGAD_\LaravelPayU\RequestBodySchemas\ShippingAddress;

class ArgentinaCard extends Payment
{
    public $amount;
    public $currency;
    public $order;
    public $statement_soft_descriptor;
    public $billing_address;
    public $shipping_address;

    function __construct(
        int $amount,
        string $currency,
        string $statement_soft_descriptor,
        Order $order = null,
        BillingAddress $billing_address = null,
        ShippingAddress $shipping_address = null
    )
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->order = $order;
        $this->statement_soft_descriptor = $statement_soft_descriptor;
        $this->billing_address = $billing_address;
        $this->shipping_address = $shipping_address;
    }
}

