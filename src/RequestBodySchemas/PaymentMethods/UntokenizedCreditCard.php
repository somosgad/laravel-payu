<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas\PaymentMethods;

class UntokenizedCreditCard extends PaymentMethod
{
    public $source_type;
    public $holder_name;
    public $card_number;
    public $expiration_date;
    public $additional_details;
    public $card_identity;
    public $credit_card_cvv;

    public function __construct(
        string $type = 'untokenized',
        string $source_type,
        string $holder_name,
        string $card_number,
        string $expiration_date = null,
        object $card_identity = null,
        string $credit_card_cvv = null
    )
    {
        parent::__construct($type);
        $this->source_type = $source_type;
        $this->holder_name = $holder_name;
        $this->card_number = $card_number;
        $this->expiration_date = $expiration_date;
        $this->card_identity = $card_identity;
        $this->credit_card_cvv = $credit_card_cvv;
    }
}
