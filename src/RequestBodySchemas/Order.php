<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas;

class Order
{
    public $tax_amount;

    function __construct(int $tax_amount)
    {
        $this->tax_amount = $tax_amount;
    }
}

