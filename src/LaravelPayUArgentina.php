<?php

namespace SomosGAD_\LaravelPayU;

class LaravelPayUArgentina extends LaravelPayUBase
{
    public function createCashPayment(
        int $amount,
        string $currency,
        string $statement_soft_descriptor
    )
    {
        return $this->createGenericPayment(
            $amount,
            $currency,
            $statement_soft_descriptor
        );
    }
}
