<?php

namespace Diskominfotik\QueryFilter\Tests;

use Orchestra\Testbench\TestCase;
use Diskominfotik\QueryFilter\QueryFilterServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [QueryFilterServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
