<?php

namespace SomosGAD_\LaravelPayU\RequestsBodySchemas\PaymentMethods;

class PaymentMethod
{
    public $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }
}
