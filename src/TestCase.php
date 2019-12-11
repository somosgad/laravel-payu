<?php

namespace SomosGAD_\LaravelPayU;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase as TestCaseBase;

class TestCase extends TestCaseBase
{
    public function __construct()
    {
        parent::__construct();
        $dir = substr(__DIR__, 0, -3); // remove "src" from dir
        $dotenv = Dotenv::createImmutable($dir);
        $dotenv->load();
    }
}
