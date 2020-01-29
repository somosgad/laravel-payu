<?php

namespace SomosGAD_\LaravelPayU\RequestBodySchemas\Token;

use SomosGAD_\LaravelPayU\RequestBodySchemas\ProviderSpecificData;
use SomosGAD_\LaravelPayU\RequestBodySchemas\ShippingAddress;

class BillingAgreement extends Token
{
    public $vendor;
    public $configuration_id;
    public $name;
    public $description;
    public $merchant_site_url;
    public $shipping_address;
    public $provider_specific_data;

    function __construct(
        string $vendor,
        string $configuration_id,
        string $name = null,
        string $description = null,
        string $merchant_site_url = null,
        ShippingAddress $shipping_address = null,
        ProviderSpecificData $provider_specific_data = null
    ) {
        $token_type = 'billing_agreement';
        parent::__construct($token_type);
        $this->vendor = $vendor;
        $this->configuration_id = $configuration_id;
        $this->name = $name;
        $this->description = $description;
        $this->merchant_site_url = $merchant_site_url;
        $this->shipping_address = $shipping_address;
        $this->provider_specific_data = $provider_specific_data;
    }

    function toArray() {
        return [
            'token_type' => $this->token_type,
            'vendor' => $this->vendor,
            'configuration_id' => $this->configuration_id,
            'name' => $this->name,
            'description' => $this->description,
            'merchant_site_url' => $this->merchant_site_url,
            'shipping_address' => $this->shipping_address,
            'provider_specific_data' => $this->provider_specific_data,
        ];
    }
}
