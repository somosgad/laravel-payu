<?php

namespace SomosGAD_\LaravelPayU\Tests;

use SomosGAD_\LaravelPayU\LaravelPayU;

class InstanceTest extends TestCase
{
    /**
     * Test package's instance.
     *
     * @return void
     */
    public function testInstance()
    {
        $payu = new LaravelPayU;

        $this->assertInstanceOf(LaravelPayU::class, $payu);

        return $payu;
    }
}
