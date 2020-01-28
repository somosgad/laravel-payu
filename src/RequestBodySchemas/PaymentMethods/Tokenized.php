<?php

namespace SomosGAD_\LaravelPayU\RequestsBodySchemas\PaymentMethods;

class Tokenized extends PaymentMethod
{
    public $token;
    public $credit_card_cvv;

    /**
     * receive:
     *  string $type
     *  string $token token which represent the billing.
     *  string $credit_card_cvv the cvv number of client card
     **/
    public function __construct(
        string $type = 'tokenized',
        string $token,
        string $credit_card_cvv = null
    )
    {
        parent::__construct($type);
        $this->token = $token;
        $this->credit_card_cvv = $credit_card_cvv;
    }
}
