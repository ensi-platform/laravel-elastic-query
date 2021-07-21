<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Raw\Aggregating\Buckets;

use Greensight\LaravelElasticQuery\Raw\Aggregating\AggregationCollection;
use Greensight\LaravelElasticQuery\Raw\Aggregating\Bucket\NestedAggregation;
use Greensight\LaravelElasticQuery\Raw\Contracts\Aggregation;
use Greensight\LaravelElasticQuery\Tests\AssertsArray;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class NestedAggregationTest extends UnitTestCase
{
    use AssertsArray;

    const AGG_NAME = 'agg_nested';
    const AGG_PATH = 'offers';
    const INNER_AGG_NAME = 'agg_inner';

    private Aggregation|LegacyMockInterface|MockInterface $mockAggregation;

    private NestedAggregation $testing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAggregation = Mockery::mock(Aggregation::class);
        $this->mockAggregation->allows('name')->andReturn(self::INNER_AGG_NAME);

        $this->testing = new NestedAggregation(
            self::AGG_NAME,
            self::AGG_PATH,
            AggregationCollection::fromAggregation($this->mockAggregation)
        );
    }

    public function testToDSL(): void
    {
        $this->mockAggregation->allows('toDSL')->andReturn([self::INNER_AGG_NAME => 'body']);

        $this->assertArrayStructure(
            [self::AGG_NAME => ['nested' => ['path'], 'aggs' => [self::INNER_AGG_NAME]]],
            $this->testing->toDSL()
        );
    }

    public function testParseResults(): void
    {
        $expected = [self::INNER_AGG_NAME => 'value'];
        $this->mockAggregation->allows('parseResults')->andReturn($expected);

        $this->assertEquals($expected, $this->testing->parseResults([self::AGG_NAME => $expected]));
    }
}
