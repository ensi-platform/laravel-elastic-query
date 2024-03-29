<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit\Search\Sorting;

use Ensi\LaravelElasticQuery\Contracts\MissingValuesMode;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Ensi\LaravelElasticQuery\Search\Sorting\NestedSort;
use Ensi\LaravelElasticQuery\Search\Sorting\Sort;
use Ensi\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class SortTest extends UnitTestCase
{
    public function testFieldOnly(): void
    {
        $this->assertEquals(['code' => 'asc'], (new Sort('code'))->toDSL());
    }

    public function testOrderDesc(): void
    {
        $this->assertEquals(
            ['code' => 'desc'],
            (new Sort('code', 'desc'))->toDSL()
        );
    }

    public function testMode(): void
    {
        $this->assertEquals(
            ['code' => ['order' => 'asc', 'mode' => 'min']],
            (new Sort('code', mode: 'min'))->toDSL()
        );
    }

    public function testMissingValuesMode(): void
    {
        $this->assertEquals(
            ['code' => ['order' => 'asc', 'missing' => '_first']],
            (new Sort('code', missingValues: MissingValuesMode::FIRST))->toDSL()
        );
    }

    public function testNested(): void
    {
        $nested = $this->newNestedSort('offers');

        $this->assertEquals(
            ['offers.price' => ['order' => 'asc', 'nested' => ['path' => 'offers']]],
            (new Sort('offers.price', nested: $nested))->toDSL()
        );
    }

    public function testInvert(): void
    {
        $testing = new Sort('code', 'desc');

        $this->assertEquals(['code' => ['order' => 'asc', 'missing' => '_first']], $testing->invert()->toDSL());
    }

    public function testInvertNested(): void
    {
        $testing = new Sort(
            'offers.price',
            order: 'desc',
            nested: $this->newNestedSort('offers')
        );

        $this->assertEquals(
            $testing->toDSL(),
            $testing->invert()->invert()->toDSL()
        );
    }

    public function testToString(): void
    {
        $this->assertEquals('+code', (string)(new Sort('code')));
    }

    private function newNestedSort(string $path): NestedSort
    {
        return new NestedSort($path, new BoolQueryBuilder(emptyMatchAll: false));
    }
}
