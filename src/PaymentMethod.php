<?php

namespace SomosGAD_\LaravelPayU;

class PaymentMethod
{
    public $source_type;
    public $type;
    public $vendor;
    public $additional_details;

    public function __construct(
        string $source_type,
        string $type,
        string $vendor,
        AdditionalDetails $additional_details
    )
    {
        $this->source_type = $source_type;
        $this->type = $type;
        $this->vendor = $vendor;
        $this->additional_details = $additional_details;
    }
}
