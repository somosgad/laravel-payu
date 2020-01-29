<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas\Payment;

use SomosGAD_\LaravelPayU\RequestBodySchemas\AdditionalDetails;
use SomosGAD_\LaravelPayU\RequestBodySchemas\BillingAddress;
use SomosGAD_\LaravelPayU\RequestBodySchemas\Order;
use SomosGAD_\LaravelPayU\RequestBodySchemas\ShippingAddress;

abstract class Payment
{
    public $amount;
    public $currency;
    public $additional_details;
    public $statement_soft_descriptor;
    public $order;
    public $customer_id;
    public $shipping_address;
    public $billing_address;
    public $addendums;

    public function __construct(
        int $amount,
        string $currency,
        AdditionalDetails $additional_details = null,
        string $statement_soft_descriptor = null,
        Order $order = null,
        string $customer_id = null,
        ShippingAddress $shipping_address = null,
        BillingAddress $billing_address = null,
        object $addendums = null
    ) {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->additional_details = $additional_details;
        $this->statement_soft_descriptor = $statement_soft_descriptor;
        $this->order = $order;
        $this->customer_id = $customer_id;
        $this->shipping_address = $shipping_address;
        $this->billing_address = $billing_address;
        $this->addendums = $addendums;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'additional_details' => $this->additional_details,
            'statement_soft_descriptor' => $this->statement_soft_descriptor,
            'order' => $this->order,
            'customer_id' => $this->customer_id,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'addendums' => $this->addendums,
        ], 'is_not_null');
    }
}
