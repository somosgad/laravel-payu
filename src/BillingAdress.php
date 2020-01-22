<?php

namespace SomosGAD_\LaravelPayU;

class BillingAddress
{
    public $country;
    public $first_name;
    public $last_name;

    public function __construct(
        string $country, string $first_name = null, string $last_name = null
    )
    {
        $this->country = $country;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }
}
