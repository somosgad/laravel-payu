<?php

namespace SomosGAD_\LaravelPayU\Providers\PayUChile\Payment;

use SomosGAD_\LaravelPayU\PaymentMethodTypes\Cash as CashBase;

class Cash extends CashBase
{
    public $amount;
    public $currency;
    public $statement_soft_descriptor;

    function __construct(
        int $amount, string $currency, string $statement_soft_descriptor
    )
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->statement_soft_descriptor = $statement_soft_descriptor;
    }
}
