<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Aggregating;

use Greensight\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Greensight\LaravelElasticQuery\Aggregating\MinMax;
use Greensight\LaravelElasticQuery\Contracts\AggregationsBuilder;
use Greensight\LaravelElasticQuery\Tests\Functional\ElasticTestCase;
use Greensight\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Greensight\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;

class AggregationQueryTest extends ElasticTestCase
{
    private AggregationsQuery $testing;

    protected function setUp(): void
    {
        parent::setUp();

        ProductIndexSeeder::run();
        $this->testing = $this->makeAggregationsQuery(ProductsIndex::class);
    }

    public function testGet(): void
    {
        $this->testing
            ->where('package', 'bottle')
            ->terms('codes', 'code')
            ->nested(
                'offers',
                fn (AggregationsBuilder $builder) => $builder->where('seller_id', 10)->minmax('price', 'price')
            );

        $results = $this->testing->get();

        $this->assertEqualsCanonicalizing(
            ['voda-san-pellegrino-mineralnaya-gazirovannaya', 'water'],
            $results->get('codes')->pluck('key')->all()
        );
        $this->assertEquals(new MinMax(168.0, 611.0), $results->get('price'));
    }

    public function testComposite(): void
    {
        $this->testing->composite(function (AggregationsBuilder $builder) {
            $builder->where('package', 'bottle')
                ->terms('codes', 'code');
        });

        $results = $this->testing->get();

        $this->assertEqualsCanonicalizing(
            ['voda-san-pellegrino-mineralnaya-gazirovannaya', 'water'],
            $results->get('codes')->pluck('key')->all()
        );
    }
}
