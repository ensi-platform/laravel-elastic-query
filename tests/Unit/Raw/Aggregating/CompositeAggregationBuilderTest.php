<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Raw\Aggregating;

use Greensight\LaravelElasticQuery\Raw\Aggregating\CompositeAggregationBuilder;
use Greensight\LaravelElasticQuery\Raw\Contracts\AggregationsBuilder;
use Greensight\LaravelElasticQuery\Tests\AssertsArray;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class CompositeAggregationBuilderTest extends UnitTestCase
{
    use AssertsArray;

    const NAME = 'root';

    public function testBuildEmpty(): void
    {
        $this->assertTrue($this->createBuilder()->build()->isEmpty());
    }

    public function testBuildWithoutFilter(): void
    {
        $testing = $this->createBuilder()->terms('test', 'test');

        $this->assertArrayStructure(['test' => ['terms']], $testing->toDSL());
    }

    public function testBuildWithFilter(): void
    {
        $testing = $this->createBuilder()
            ->where('code', 'tv')
            ->terms('test', 'test');

        $this->assertArrayStructure([self::NAME => ['filter', 'aggs']], $testing->toDSL());
    }

    public function testPathFilter(): void
    {
        $testing = $this->createBuilder('offers')
            ->where('seller_id', 15)
            ->terms('prices', 'price');

        $this->assertArrayFragment(['offers.seller_id' => 15], $testing->toDSL());
    }

    public function testPathAggregation(): void
    {
        $testing = $this->createBuilder('offers')->terms('prices', 'price');

        $this->assertArrayFragment(['field' => 'offers.price'], $testing->toDSL());
    }

    public function testNested(): void
    {
        $testing = $this->createBuilder()
            ->nested('offers', function (AggregationsBuilder $builder) {
                $builder->terms('external', 'external_id');
            });

        $this->assertArrayStructure([self::NAME.'_1' => ['nested']], $testing->toDSL());
    }

    public function testNestedWithFilter(): void
    {
        $name = self::NAME . '_1';

        $testing = $this->createBuilder()
            ->nested('offers', function (AggregationsBuilder $builder) {
                $builder->where('seller_id', 15)->terms('external', 'external_id');
            });

        $this->assertArrayStructure(
            [$name => ['nested', 'aggs' => ["{$name}_filter" => ['filter']]]],
            $testing->toDSL()
        );
    }

    private function createBuilder(string $path = ''): CompositeAggregationBuilder
    {
        return new CompositeAggregationBuilder(self::NAME, $path);
    }
}
