<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas;

class ShippingAddress
{
    public $city;
    public $country;
    public $email;
    public $first_name;
    public $last_name;
    public $line1;
    public $line2;
    public $phone;
    public $state;
    public $zip_code;

    public function __construct(
        string $city = null,
        string $country = null,
        string $email = null,
        string $first_name = null,
        string $last_name = null,
        string $line1 = null,
        string $line2 = null,
        string $phone = null,
        string $state = null,
        string $zip_code = null
    ) {
        $this->city = $city;
        $this->country = $country;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->phone = $phone;
        $this->state = $state;
        $this->zip_code = $zip_code;
    }

    public function toArray()
    {
        return array_filter([
            'city' => $this->city,
            'country' => $this->country,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'phone' => $this->phone,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
        ], 'is_not_null');
    }
}
