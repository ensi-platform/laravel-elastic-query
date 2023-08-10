<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit\Search\Sorting;

use Ensi\LaravelElasticQuery\Search\Collapsing\Collapse;
use Ensi\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class CollapseTest extends UnitTestCase
{
    public function testFieldOnly(): void
    {
        $this->assertEquals(['field' => 'product_id'], (new Collapse('product_id'))->toDSL());
    }
}
