<?php

namespace Greensight\LaravelElasticQuery\Tests;

use Illuminate\Testing\AssertableJsonString;

trait AssertsArray
{
    protected function assertArrayStructure(array $expected, array $actual): void
    {
        $this->makeAssertableArray($actual)->assertStructure($expected);
    }

    protected function assertArrayFragment(array $expected, array $actual): void
    {
        $this->makeAssertableArray($actual)->assertFragment($expected);
    }

    private function makeAssertableArray(array $source): AssertableJsonString
    {
        return new AssertableJsonString($source);
    }
}
