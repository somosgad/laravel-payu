<?php

namespace SomosGAD_\LaravelPayU\RequestsSchemas\PaymentMethod;

class PaymentMethod
{
    public $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }
}
