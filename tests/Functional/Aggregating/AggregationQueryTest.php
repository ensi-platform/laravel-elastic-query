<?php

namespace Ensi\LaravelElasticQuery\Tests\Functional\Aggregating;

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\Aggregating\MinMax;
use Ensi\LaravelElasticQuery\Contracts\AggregationsBuilder;
use Ensi\LaravelElasticQuery\Tests\Functional\ElasticTestCase;
use Ensi\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Ensi\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;

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
            ->count('product_count', 'product_id')
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
        $this->assertEquals(2, $results->get('product_count'));
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

    public function testCountAll(): void
    {
        $this->testing->count('product_count', 'product_id');

        $results = $this->testing->get();

        $this->assertEquals(self::TOTAL_PRODUCTS, $results->get('product_count'));
    }

    public function testTermsSize(): void
    {
        $this->testing
            ->where('package', 'bottle')
            ->terms('codes', 'code', 1);

        $results = $this->testing->get();

        $this->assertCount(1, $results->get('codes'));
    }
}
