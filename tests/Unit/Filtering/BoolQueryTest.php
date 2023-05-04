<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit\Filtering;

use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuery\Contracts\MatchOptions;
use Ensi\LaravelElasticQuery\Contracts\MatchType;
use Ensi\LaravelElasticQuery\Contracts\MultiMatchOptions;
use Ensi\LaravelElasticQuery\Contracts\WildcardOptions;
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

    /**
     * @dataProvider provideMatch
     */
    public function testMatch(string|MatchOptions $options, array $expected): void
    {
        $dsl = BoolQueryBuilder::make()->whereMatch('name', 'foo', $options)->toDSL();

        $this->assertArrayFragment(array_merge(['query' => 'foo'], $expected), $dsl);
    }

    public function provideMatch(): array
    {
        return [
            'operator' => ['and', ['operator' => 'and']],
            'fuzziness' => [MatchOptions::make(fuzziness: 'AUTO'), ['fuzziness' => 'AUTO']],
            'minimum_should_match' => [
                MatchOptions::make(minimumShouldMatch: '50%'),
                ['minimum_should_match' => '50%'],
            ],
            'many options' => [
                MatchOptions::make(operator: 'or', fuzziness: '2', minimumShouldMatch: '30%'),
                ['minimum_should_match' => '30%', 'fuzziness' => '2', 'operator' => 'or'],
            ],
        ];
    }

    /**
     * @dataProvider provideMultiMatch
     */
    public function testMultiMatch(string|MultiMatchOptions|null $options, array $expected): void
    {
        $dsl = BoolQueryBuilder::make()->whereMultiMatch(['foo', 'bar'], 'baz', $options)->toDSL();

        $this->assertArrayFragment(array_merge(['query' => 'baz', 'fields' => ['foo', 'bar']], $expected), $dsl);
    }

    public function provideMultiMatch(): array
    {
        return [
            'type as string' => [MatchType::CROSS_FIELDS, ['type' => MatchType::CROSS_FIELDS]],
            'type in options' => [MultiMatchOptions::make(MatchType::PHRASE), ['type' => MatchType::PHRASE]],
            'fuzziness' => [MultiMatchOptions::make(fuzziness: 'AUTO'), ['fuzziness' => 'AUTO']],
            'multiple options' => [
                MultiMatchOptions::make(type: MatchType::MOST_FIELDS, fuzziness: '3', minimumShouldMatch: '30%'),
                ['minimum_should_match' => '30%', 'fuzziness' => '3', 'type' => MatchType::MOST_FIELDS],
            ],
        ];
    }

    /**
     * @dataProvider provideWildcard
     */
    public function testWildcard(?WildcardOptions $options, array $expected): void
    {
        $dsl = BoolQueryBuilder::make()->whereWildcard('foo', '%value%', $options)->toDSL();

        $this->assertArrayFragment(['wildcard' => ['foo' => array_merge(['value' => '%value%'], $expected)]], $dsl);
    }

    public function provideWildcard(): array
    {
        return [
            'empty options' => [WildcardOptions::make(0, false), ['boost' => 0, 'case_insensitive' => false]],
            'full options' => [WildcardOptions::make(0.5, true), ['boost' => 0.5, 'case_insensitive' => true]],
            'rewrite options' => [WildcardOptions::make(rewrite: 'constant_score'), ['rewrite' => 'constant_score']],
        ];
    }
}
