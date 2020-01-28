<?php

namespace SomosGAD_\LaravelPayU\Providers\PayUArgentina\Payment;

use SomosGAD_\LaravelPayU\PaymentMethodTypes\Card as CardBase;
use SomosGAD_\LaravelPayU\RequestsBodySchemas\BillingAddress;
use SomosGAD_\LaravelPayU\RequestsBodySchemas\Order;
use SomosGAD_\LaravelPayU\RequestsBodySchemas\ShippingAddress;

class Card extends CardBase
{
    public $amount;
    public $currency;
    public $order;
    public $statement_soft_descriptor;
    public $billing_address;
    public $shipping_address;

    /*
     * @param Order $order Notes specific to all PayU Latam providers. Only required if the tax_amount for the payment is higher than 0. For a general description of this field, see the API Reference.
     */
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

