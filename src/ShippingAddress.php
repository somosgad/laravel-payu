<?php

namespace SomosGAD_\LaravelPayU;

class ShippingAddress
{
    public $country;
    public $city;
    public $first_name;
    public $last_name;

    public function __construct(
        string $country,
        string $city = null,
        string $email = null,
        string $first_name = null,
        string $last_name = null,
        string $line1 = null,
        string $line2 = null,
        string $phone = null,
        string $state = null,
        string $zip_code = null
    )
    {
        $this->country = $country;
        $this->city = $city;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->phone = $phone;
        $this->state = $state;
        $this->zip_code = $zip_code;
    }
}
