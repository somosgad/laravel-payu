<?php

namespace SomosGAD_\LaravelPayU\RequestsBodySchemas\Token;

use SomosGAD_\LaravelPayU\RequestsBodySchemas\AdditionalDetails;
use SomosGAD_\LaravelPayU\RequestsBodySchemas\BillingAddress;
use SomosGAD_\LaravelPayU\RequestsBodySchemas\ShippingAddress;

class CreditCard extends Token
{
    public $holder_name;
    public $card_number;
    public $expiration_date;
    public $identity_document;
    public $shipping_address;
    public $billing_address;
    public $additional_details;
    private $_credit_card_cvv;

    function __construct(
        string $holder_name,
        string $card_number,
        string $expiration_date = null,
        object $identity_document = null,
        ShippingAddress $shipping_address = null,
        BillingAddress $billing_address = null,
        AdditionalDetails $additional_details = null,
        int $credit_card_cvv = null
    ) {
        $token_type = 'credit_card';
        parent::__construct($token_type);
        $this->holder_name = $holder_name;
        $this->card_number = $card_number;
        $this->expiration_date = $expiration_date;
        $this->identity_document = $identity_document;
        $this->shipping_address = $shipping_address;
        $this->billing_address = $billing_address;
        $this->additional_details = $additional_details;
        $this->setCreditCardCVV($credit_card_cvv);
    }

    function getCreditCardCVV()
    {
        return $this->_credit_card_cvv;
    }

    function setCreditCardCVV($value)
    {
        $this->_credit_card_cvv = (string) $value;
    }

    function toArray() {
        return [
            'token_type' => $this->token_type,
            'holder_name' => $this->holder_name,
            'card_number' => $this->card_number,
            'expiration_date' => $this->expiration_date,
            'identity_document' => $this->identity_document,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'additional_details' => $this->additional_details,
            'credit_card_cvv' => $this->_credit_card_cvv,
        ];
    }
}
