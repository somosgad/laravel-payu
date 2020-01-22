<?php

namespace SomosGAD_\LaravelPayU;

class AdditionalDetails
{
    public $order_language;
    public $cash_payment_method_vendor;
    public $payment_method;
    public $payment_country;

    public function __construct(
        string $order_language,
        string $cash_payment_method_vendor,
        string $payment_method,
        string $payment_country
    )
    {
        $this->order_language = $order_language;
        $this->cash_payment_method_vendor = $cash_payment_method_vendor;
        $this->payment_method = $payment_method;
        $this->payment_country = $payment_country;
    }
}
