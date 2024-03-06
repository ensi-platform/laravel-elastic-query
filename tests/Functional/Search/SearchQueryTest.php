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

    /**
     * @dataProvider providerSortByCustomArray
     */
    public function testSortByCustomArray(array $items, array $documents): void
    {
        $this->testing->sortByCustomArray('product_id', $items)->take(3);

        $this->assertDocumentIds($documents);
    }

    public static function providerSortByCustomArray(): array
    {
        return [
            'all_first' => [[150, 1, 319], [150, 1, 319]],
            'all_second' => [[319, 150, 1], [319, 150, 1]],
            'extra' => [[319, 150, 1, 328], [319, 150, 1]],
            'mixed' => [[123456789, 319, 150], [319, 150, 1]],
        ];
    }

    public function testSelect(): void
    {
        $this->testing->select(['product_id'])->take(1);
        $result = $this->testing->get();

        $this->assertEquals(1, $result[0]['_source']['product_id']);
        $this->assertArrayNotHasKey('name', $result[0]['_source']);
    }

    public function testExclude(): void
    {
        $this->testing->exclude(['product_id'])->take(1);
        $result = $this->testing->get();

        $this->assertArrayNotHasKey('product_id', $result[0]['_source']);
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

    //region Collapsing
    public function testCollapse(): void
    {
        $results = $this->testing->collapse('vat')->get();

        $this->assertCount(2, $results);
    }

    //endregion
}
