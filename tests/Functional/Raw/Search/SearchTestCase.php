<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Raw\Search;

use Greensight\LaravelElasticQuery\Raw\Search\SearchQuery;
use Greensight\LaravelElasticQuery\Tests\Functional\ElasticTestCase;
use Greensight\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Greensight\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;

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
}
