<?php

namespace SomosGAD_\LaravelPayU\RequestsBodySchemas\Token;

class CardCVVCode extends Token
{
    public $payment_method_token;
    private $_credit_card_cvv;

    function __construct(
        string $payment_method_token,
        int $credit_card_cvv
    ) {
        $token_type = 'card_cvv_code';
        parent::__construct($token_type);
        $this->payment_method_token = $payment_method_token;
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
            'payment_method_token' => $this->payment_method_token,
            'credit_card_cvv' => $this->_credit_card_cvv,
        ];
    }
}
