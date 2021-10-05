<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit\Filtering;

use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Ensi\LaravelElasticQuery\Tests\AssertsArray;
use Ensi\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use stdClass;

class BoolQueryTest extends UnitTestCase
{
    use AssertsArray;

    /**
     * @dataProvider provideFilter
     */
    public function testFilter(BoolQueryBuilder $query, array $expected): void
    {
        $this->assertArrayStructure(['bool' => ['filter' => [$expected]]], $query->toDSL());
    }

    public function provideFilter(): array
    {
        return [
            'term' => [
                BoolQueryBuilder::make()
                    ->where('name', 'Product')
                    ->where('active', true),
                ['term'],
            ],
            'nested' => [
                BoolQueryBuilder::make('offers')
                    ->whereHas(
                        'offers',
                        fn (BoolQuery $query) => $query->where('name', 'Product')
                    )
                    ->where('active', true),
                ['nested' => ['path', 'query']],
            ],
        ];
    }

    /**
     * @dataProvider provideMustNot
     */
    public function testMustNot(BoolQueryBuilder $query, array $expected): void
    {
        $this->assertArrayStructure(['bool' => ['must_not' => [$expected]]], $query->toDSL());
    }

    public function provideMustNot(): array
    {
        return [
            'term' => [BoolQueryBuilder::make()->whereNot('name', 'Product'), ['term']],
            'nested' => [
                BoolQueryBuilder::make('offers')->whereDoesntHave(
                    'offers',
                    fn (BoolQueryBuilder $query) => $query->where('name', 'Product')
                ),
                ['nested' => ['path', 'query']],
            ],
        ];
    }

    /**
     * @dataProvider provideEmpty
     */
    public function testEmptyMatchAll(BoolQueryBuilder $query): void
    {
        $this->assertTrue($query->isEmpty());
        $this->assertArrayFragment(['match_all' => new stdClass()], $query->toDSL());
    }

    public function provideEmpty(): array
    {
        return [
            'empty' => [BoolQueryBuilder::make()],
            'nested without body' => [BoolQueryBuilder::make()->whereHas('offers', fn () => null)],
        ];
    }

    public function testNotEmptyMatchAll(): void
    {
        $query = new BoolQueryBuilder('', false);

        $this->assertEquals([], $query->toDSL());
    }

    public function testPath(): void
    {
        $this->assertArrayFragment(
            ['term' => ['offers.seller_id' => 10]],
            BoolQueryBuilder::make('offers')->where('seller_id', 10)->toDSL()
        );
    }

    public function testMultiLevelNested(): void
    {
        $query = BoolQueryBuilder::make()
            ->whereHas('offers', function (BoolQuery $query) {
                $query->whereHas('stocks', fn (BoolQuery $query) => $query->where('stock', 0));
            });

        $this->assertArrayFragment(
            ['term' => ['offers.stocks.stock' => 0]],
            $query->toDSL()
        );
    }

    /**
     * @dataProvider provideWhereOperators
     */
    public function testWhereOperators(string $operator, array $expected): void
    {
        $dsl = BoolQueryBuilder::make()->where('rating', $operator, 5)->toDSL();

        $this->assertArrayFragment($expected, $dsl);
    }

    public function provideWhereOperators(): array
    {
        return [
            '=' => ['=', ['term' => ['rating' => 5]]],
            '!=' => ['!=', ['must_not' => [['term' => ['rating' => 5]]]]],
            '>' => ['>', ['range' => ['rating' => ['gt' => 5]]]],
            '>=' => ['>=', ['range' => ['rating' => ['gte' => 5]]]],
            '<' => ['<', ['range' => ['rating' => ['lt' => 5]]]],
            '<=' => ['<=', ['range' => ['rating' => ['lte' => 5]]]],
        ];
    }
}
