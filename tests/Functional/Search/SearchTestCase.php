<?php

namespace Ensi\LaravelElasticQuery\Tests\Functional\Search;

use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Tests\Functional\ElasticTestCase;
use Ensi\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Ensi\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;

class SearchTestCase extends ElasticTestCase
{
    const TOTAL_PRODUCTS = 6;

    protected SearchQuery $testing;

    protected function setUp(): void
    {
        parent::setUp();

        ProductIndexSeeder::run();
        $this->testing = $this->makeSearchQuery(ProductsIndex::class);
    }

    protected function assertDocumentIds(array $expected): void
    {
        $actual = $this->testing->get()
            ->pluck('_id')
            ->all();

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function assertDocumentOrder(array $ids): void
    {
        $actual = $this->testing->get()
            ->pluck('_id')
            ->all();

        $this->assertEquals($ids, $actual);
    }
}
