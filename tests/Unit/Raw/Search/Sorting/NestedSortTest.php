<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Raw\Search\Sorting;

use Greensight\LaravelElasticQuery\Raw\Filtering\BoolQueryBuilder;
use Greensight\LaravelElasticQuery\Raw\Search\Sorting\NestedSort;
use Greensight\LaravelElasticQuery\Tests\AssertsArray;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class NestedSortTest extends UnitTestCase
{
    use AssertsArray;

    public function testToDSL(): void
    {
        $this->assertEquals(['path' => 'code'], $this->createTesting('code')->toDSL());
    }

    public function testToDSLWithFilter(): void
    {
        $filter = $this->createBoolQuery('offers')->where('seller_id', 10);

        $this->assertArrayStructure(
            ['path', 'filter'],
            $this->createTesting('offers', $filter)->toDSL()
        );
    }

    public function testDSLWithNested(): void
    {
        $nested = $this->createTesting('offers.stocks');

        $this->assertArrayStructure(
            ['path', 'nested' => ['path']],
            $this->createTesting('offers', nested: $nested)->toDSL()
        );
    }

    private function createTesting(string $path, ?BoolQueryBuilder $query = null, ?NestedSort $nested = null): NestedSort
    {
        return new NestedSort($path, $query ?? $this->createBoolQuery(), $nested);
    }

    private function createBoolQuery(string $path = ''): BoolQueryBuilder
    {
        return new BoolQueryBuilder($path, false);
    }
}
