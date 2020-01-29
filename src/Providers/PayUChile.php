<?php

namespace SomosGAD_\LaravelPayU\Providers;

use Exception;
use SomosGAD_\LaravelPayU\Providers\PayULatam;
use SomosGAD_\LaravelPayU\RequestBodySchemas\Payment\Payment;

class PayUChile extends PayULatam
{
    private $currencies = ['CLP'];

    public function createPayment2(Payment $payment)
    {
        if ( ! in_array($payment->currency, $this->currencies)) {
            throw new Exception("PayU Chile doesn't support $payment->currency currency.");
        }
        return parent::createPayment2($payment);
    }
}
