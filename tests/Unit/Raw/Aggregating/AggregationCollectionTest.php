<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Raw\Aggregating;

use Greensight\LaravelElasticQuery\Raw\Aggregating\AggregationCollection;
use Greensight\LaravelElasticQuery\Raw\Contracts\Aggregation;
use Greensight\LaravelElasticQuery\Tests\AssertsArray;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use InvalidArgumentException;
use Mockery;

class AggregationCollectionTest extends UnitTestCase
{
    use AssertsArray;

    public function testEmptyToDSL(): void
    {
        $testing = new AggregationCollection();

        $this->assertEquals([], $testing->toDSL());
    }

    public function testConstructFromAggregation(): void
    {
        $testing = AggregationCollection::fromAggregation($this->mockAggregation('agg1'));

        $this->assertEquals(1, $testing->count());
        $this->assertFalse($testing->isEmpty());
    }

    public function testAdd(): void
    {
        $expected = ['agg2' => 'body'];
        $testing = new AggregationCollection();
        $testing->add($this->mockAggregation('agg2', $expected));

        $this->assertEquals($expected, $testing->toDSL());
    }

    public function testAddAlreadyExistingName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $testing = new AggregationCollection();

        $testing->add($this->mockAggregation('agg'));
        $testing->add($this->mockAggregation('agg'));
    }

    /**
     * @dataProvider provideGenerateUniqueName
     */
    public function testGenerateUniqueName(AggregationCollection $target, string $name, string $expected): void
    {
        $this->assertEquals($expected, $target->generateUniqueName($name));
    }

    public function provideGenerateUniqueName(): array
    {
        return [
            'no items no name' => [new AggregationCollection(), ' ', 'agg_1'],
            'no items with name' => [new AggregationCollection(), 'agg_2', 'agg_2_1'],
            'has items' => [
                AggregationCollection::fromAggregation($this->mockAggregation('agg_1')),
                '',
                'agg_2',
            ],
        ];
    }

    public function testMergeNonUniqueNames(): void
    {
        $source = AggregationCollection::fromAggregation($this->mockAggregation('agg1'));
        $testing = AggregationCollection::fromAggregation($this->mockAggregation('agg1'));

        $this->expectException(InvalidArgumentException::class);

        $testing->merge($source);
    }

    public function testToDSLKeepsReturnedNames(): void
    {
        $testing = new AggregationCollection();
        $testing->add($this->mockAggregation('agg1', ['foo' => 'body']));
        $testing->add($this->mockAggregation('agg2', ['bar' => 'body']));

        $this->assertArrayStructure(['foo', 'bar'], $testing->toDSL());
    }

    public function testParseResults(): void
    {
        $testing = new AggregationCollection();
        $testing->add($this->mockAggregation('agg1', null, ['foo' => 'result']));
        $testing->add($this->mockAggregation('agg2', null, ['bar' => [20]]));

        $this->assertEquals(['foo' => 'result', 'bar' => [20]], $testing->parseResults([])->all());
    }

    private function mockAggregation(string $name, ?array $dsl = null, ?array $results = null): Aggregation
    {
        $agg = Mockery::mock(Aggregation::class);
        $agg->allows('name')->andReturn($name);

        if ($dsl !== null) {
            $agg->allows('toDSL')->andReturn($dsl);
        }

        if ($results !== null) {
            $agg->allows('parseResults')->andReturn($results);
        }

        return $agg;
    }
}
