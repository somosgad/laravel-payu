<?php

namespace SomosGAD_\LaravelPayU\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelPayU extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-payu';
    }
}
