<?php

namespace SomosGAD_\LaravelPayU\RequestsSchemas\PaymentMethod;

use SomosGAD_\LaravelPayU\AdditionalDetails;

class UntokenizedAlternativePayment extends PaymentMethod
{
    public $source_type;
    public $vendor;
    public $additional_details;

    public function __construct(
        string $type = 'untokenized',
        string $source_type,
        string $vendor = null,
        AdditionalDetails $additional_details = null
    )
    {
        parent::__construct($type);
        $this->source_type = $source_type;
        $this->vendor = $vendor;
        $this->additional_details = $additional_details;
    }
}
