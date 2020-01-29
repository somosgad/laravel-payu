<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\PayUArgentina;

use SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\Payment;

class ArgentinaCash extends Payment
{
    function __construct(
        int $amount,
        string $currency,
        string $statement_soft_descriptor
    ) {
        $additional_details = null;
        parent::__construct(
            $amount,
            $currency,
            $additional_details,
            $statement_soft_descriptor
        );
    }
}
