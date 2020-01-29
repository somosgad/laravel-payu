<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas\PaymentMethods;

class PaymentMethod
{
    public $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }
}
