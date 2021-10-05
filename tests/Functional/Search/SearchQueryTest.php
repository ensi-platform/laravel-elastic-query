<?php

namespace Ensi\LaravelElasticQuery\Tests\Functional\Search;

use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuery\Contracts\SortableQuery;

class SearchQueryTest extends SearchTestCase
{
    //region Get
    public function testGetAll(): void
    {
        $results = $this->testing->get();

        $this->assertCount(self::TOTAL_PRODUCTS, $results);
    }

    public function testGetFiltered(): void
    {
        $results = $this->testing
            ->where('active', true)
            ->whereDoesntHave('offers', fn (BoolQuery $query) => $query->where('seller_id', 90))
            ->get();

        $this->assertCount(4, $results);
    }

    public function testTake(): void
    {
        $results = $this->testing->take(1)->get();

        $this->assertCount(1, $results);
    }

    public function testSkip(): void
    {
        $this->testing->skip(1)->take(1);

        $this->assertDocumentIds([150]);
    }

    //endregion

    //region Sorting
    public function testSortBy(): void
    {
        $this->testing->sortBy('product_id')->take(3);

        $this->assertDocumentIds([1, 150, 319]);
    }

    public function testSortByNested(): void
    {
        $filter = function (BoolQuery $query) {
            $query->where('seller_id', 20)
                ->where('active', true);
        };

        $this->testing
            ->whereHas('offers', $filter)
            ->sortByNested('offers', function (SortableQuery $builder) use ($filter) {
                $filter($builder);
                $builder->sortBy('price');
            })
            ->sortBy('product_id', 'desc');

        $this->assertDocumentOrder([150, 1, 328]);
    }

    //endregion

    protected function assertDocumentOrder(array $ids): void
    {
        $actual = $this->testing->get()
            ->pluck('_id')
            ->all();

        $this->assertEquals($ids, $actual);
    }
}
