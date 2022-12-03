<?php

namespace Ensi\LaravelElasticQuery\Tests\Functional;

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\ElasticIndex;
use Ensi\LaravelElasticQuery\ElasticQuery;
use Ensi\LaravelElasticQuery\ElasticQueryServiceProvider;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Orchestra\Testbench\TestCase;

class ElasticTestCase extends TestCase
{
    const TOTAL_PRODUCTS = 6;

    protected function getPackageProviders($app): array
    {
        return [
            ElasticQueryServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('tests.recreate_index', env('RECREATE_INDEX', true));
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
