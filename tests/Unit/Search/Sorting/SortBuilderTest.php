<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Search\Sorting;

use Greensight\LaravelElasticQuery\Contracts\SortableQuery;
use Greensight\LaravelElasticQuery\Search\Sorting\SortBuilder as SortBuilderImpl;
use Greensight\LaravelElasticQuery\Search\Sorting\SortCollection;
use Greensight\LaravelElasticQuery\Tests\AssertsArray;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class SortBuilderTest extends UnitTestCase
{
    use AssertsArray;

    private SortCollection $sorts;
    private SortBuilderImpl $testing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sorts = new SortCollection();
        $this->testing = new SortBuilderImpl($this->sorts);
    }

    public function testSortBy(): void
    {
        $this->testing
            ->sortBy('code', 'desc')
            ->sortBy('name');

        $this->assertArrayStructure([['code'], ['name']], $this->buildDSL());
    }

    public function testSortByNested(): void
    {
        $this->testing
            ->sortByNested('offers', fn (SortableQuery $query) => $query->sortBy('price'))
            ->sortByNested('properties', fn (SortableQuery $query) => $query->sortBy('code'));

        $this->assertArrayStructure(
            [['offers.price' => ['nested']], ['properties.code' => ['nested']]],
            $this->buildDSL()
        );
    }

    public function testSortByNestedMultiLevel(): void
    {
        $this->testing->sortByNested('offers', function (SortableQuery $query) {
            $query->sortByNested(
                'stocks',
                fn (SortableQuery $inner) => $inner->sortBy('stock')
            );
        });

        $this->assertArrayStructure([['offers.stocks.stock' => ['nested']]], $this->buildDSL());
    }

    public function testSortByNestedWithFilter(): void
    {
        $this->testing->sortByNested('offers', function (SortableQuery $query) {
            $query->sortByNested(
                'stocks',
                fn (SortableQuery $inner) => $inner->where('store_id', 150)->sortBy('stock')
            );
        });

        $this->assertArrayFragment(['offers.stocks.store_id' => 150], $this->buildDSL());
    }

    private function buildDSL(): array
    {
        return $this->sorts->toDSL();
    }
}
