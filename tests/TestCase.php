<?php

namespace SomosGAD_\LaravelPayU\Tests;

use Dotenv\Dotenv;
// use PHPUnit\Framework\TestCase as TestCaseBase;
use Orchestra\Testbench\TestCase as TestCaseBase;

class TestCase extends TestCaseBase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        // load dot env for phpunit
        $dir = substr(__DIR__, 0, -5); // get pah removing "tests" from it

        // immutably load dot env for dot env ^3.3
        $dotenv = Dotenv::create($dir);
        $dotenv->overload();

        // immutably load dot env fot dot env ^4.0
        // $dotenv = Dotenv::createImmutable($dir);
        // $dotenv->load();
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelPayU' => 'SomosGAD_\LaravelPayU\Facades\LaravelPayU'
        ];
    }

    protected function getPackageProviders($app)
    {
        return ['SomosGAD_\LaravelPayU\LaravelPayUServiceProvider'];
    }
}
