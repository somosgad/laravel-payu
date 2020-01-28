<?php

namespace SomosGAD_\LaravelPayU\RequestsBodySchemas\Token;

abstract class Token
{
    protected $token_type;

    public function __construct(string $token_type)
    {
        $this->token_type = $token_type;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        //
    }
}
