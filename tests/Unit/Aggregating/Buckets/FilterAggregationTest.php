<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit\Aggregating\Buckets;

use Ensi\LaravelElasticQuery\Aggregating\AggregationCollection;
use Ensi\LaravelElasticQuery\Aggregating\Bucket\FilterAggregation;
use Ensi\LaravelElasticQuery\Contracts\Aggregation;
use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Tests\AssertsArray;
use Ensi\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class FilterAggregationTest extends UnitTestCase
{
    use AssertsArray;

    const AGG_NAME = 'agg_filter';
    const INNER_AGG_NAME = 'agg_inner';

    private FilterAggregation $testing;

    private Aggregation|LegacyMockInterface|MockInterface $mockAggregation;

    private Criteria|LegacyMockInterface|MockInterface $mockCriteria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCriteria = Mockery::mock(Criteria::class);
        $this->mockAggregation = Mockery::mock(Aggregation::class);
        $this->mockAggregation->allows('name')->andReturn(self::INNER_AGG_NAME);

        $this->testing = new FilterAggregation(
            self::AGG_NAME,
            $this->mockCriteria,
            AggregationCollection::fromAggregation($this->mockAggregation)
        );
    }

    public function testToDSL(): void
    {
        $this->mockAggregation->allows('toDSL')->andReturn([self::INNER_AGG_NAME => 'value']);
        $this->mockCriteria->allows('toDSL')->andReturn(['bool' => 'body']);

        $this->assertArrayStructure(
            [self::AGG_NAME => ['filter' => ['bool'], 'aggs' => [self::INNER_AGG_NAME]]],
            $this->testing->toDSL()
        );
    }

    public function testParseResults(): void
    {
        $expected = [self::INNER_AGG_NAME => 'value'];
        $this->mockAggregation->allows('parseResults')->andReturn($expected);

        $this->assertEquals(
            $expected,
            $this->testing->parseResults([self::AGG_NAME => $expected])
        );
    }
}
