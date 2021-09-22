<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional;

use Greensight\LaravelElasticQuery\ElasticIndex;
use Greensight\LaravelElasticQuery\ElasticQuery;
use Greensight\LaravelElasticQuery\ElasticQueryServiceProvider;
use Greensight\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Greensight\LaravelElasticQuery\Search\SearchQuery;
use Orchestra\Testbench\TestCase;

class ElasticTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ElasticQueryServiceProvider::class,
        ];
    }

    protected function tearDown(): void
    {
        ElasticQuery::disableQueryLog();

        parent::tearDown();
    }

    /**
     * @param string|ElasticIndex $indexClass
     */
    protected function makeSearchQuery(string $indexClass): SearchQuery
    {
        return $indexClass::query();
    }

    /**
     * @param string|ElasticIndex $indexClass
     */
    protected function makeAggregationsQuery(string $indexClass): AggregationsQuery
    {
        return $indexClass::aggregate();
    }
}
