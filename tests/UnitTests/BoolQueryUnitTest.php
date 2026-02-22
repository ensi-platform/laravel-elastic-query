<?php

use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuery\Contracts\MatchOptions;
use Ensi\LaravelElasticQuery\Contracts\MatchType;
use Ensi\LaravelElasticQuery\Contracts\MultiMatchOptions;
use Ensi\LaravelElasticQuery\Contracts\WildcardOptions;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Prefix;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

uses(UnitTestCase::class);

test('bool query filter', function (BoolQueryBuilder $query, array $expected) {
    /** @var UnitTestCase $this */

    assertArrayStructure(['bool' => ['filter' => [$expected]]], $query->toDSL());
})->with([
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
]);

test('bool query must not', function (BoolQueryBuilder $query, array $expected) {
    /** @var UnitTestCase $this */

    assertArrayStructure(['bool' => ['must_not' => [$expected]]], $query->toDSL());
})->with([
    'term' => [BoolQueryBuilder::make()->whereNot('name', 'Product'), ['term']],
    'nested' => [
        BoolQueryBuilder::make('offers')->whereDoesntHave(
            'offers',
            fn (BoolQueryBuilder $query) => $query->where('name', 'Product')
        ),
        ['nested' => ['path', 'query']],
    ],
]);

test('bool query empty match all', function (BoolQueryBuilder $query) {
    /** @var UnitTestCase $this */

    assertTrue($query->isEmpty());
    assertArrayFragment(['match_all' => new stdClass()], $query->toDSL());
})->with([
    'empty' => [BoolQueryBuilder::make()],
    'nested without body' => [BoolQueryBuilder::make()->whereHas('offers', fn () => null)],
]);

test('bool query not empty match all', function () {
    /** @var UnitTestCase $this */

    assertEquals([], (new BoolQueryBuilder('', false))->toDSL());
});

test('bool query path', function () {
    /** @var UnitTestCase $this */

    assertArrayFragment(
        ['term' => ['offers.seller_id' => 10]],
        BoolQueryBuilder::make('offers')->where('seller_id', 10)->toDSL()
    );
});

test('bool query multi level nested', function () {
    /** @var UnitTestCase $this */

    $query = BoolQueryBuilder::make()
        ->whereHas('offers', function (BoolQuery $query) {
            $query->whereHas('stocks', fn (BoolQuery $query) => $query->where('stock', 0));
        });

    assertArrayFragment(
        ['term' => ['offers.stocks.stock' => 0]],
        $query->toDSL()
    );
});

test('bool query where operators', function (string $operator, array $expected) {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()->where('rating', $operator, 5)->toDSL();

    assertArrayFragment($expected, $dsl);
})->with([
    '=' => ['=', ['term' => ['rating' => 5]]],
    '!=' => ['!=', ['must_not' => [['term' => ['rating' => 5]]]]],
    '>' => ['>', ['range' => ['rating' => ['gt' => 5]]]],
    '>=' => ['>=', ['range' => ['rating' => ['gte' => 5]]]],
    '<' => ['<', ['range' => ['rating' => ['lt' => 5]]]],
    '<=' => ['<=', ['range' => ['rating' => ['lte' => 5]]]],
]);

test('bool query match', function (string|MatchOptions $options, array $expected) {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()->whereMatch('name', 'foo', $options)->toDSL();

    assertArrayFragment(['must' => [["match" => ['name' => array_merge(['query' => 'foo'], $expected)]]]], $dsl);
})->with([
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
]);

test('bool query or match', function (string|MatchOptions $options, array $expected) {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()->orWhereMatch('name', 'foo', $options)->toDSL();

    assertArrayFragment(['should' => [["match" => ['name' => array_merge(['query' => 'foo'], $expected)]]]], $dsl);
})->with([
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
]);

test('bool query multi match', function (string|MultiMatchOptions|null $options, array $expected) {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()->whereMultiMatch(['foo', 'bar'], 'baz', $options)->toDSL();

    assertArrayFragment(array_merge(['query' => 'baz', 'fields' => ['foo', 'bar']], $expected), $dsl);
})->with([
    'type as string' => [MatchType::CROSS_FIELDS, ['type' => MatchType::CROSS_FIELDS]],
    'type in options' => [MultiMatchOptions::make(MatchType::PHRASE), ['type' => MatchType::PHRASE]],
    'fuzziness' => [MultiMatchOptions::make(fuzziness: 'AUTO'), ['fuzziness' => 'AUTO']],
    'multiple options' => [
        MultiMatchOptions::make(type: MatchType::MOST_FIELDS, fuzziness: '3', minimumShouldMatch: '30%'),
        ['minimum_should_match' => '30%', 'fuzziness' => '3', 'type' => MatchType::MOST_FIELDS],
    ],
]);

test('bool query or multi match', function (string|MultiMatchOptions|null $options, array $expected) {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()->OrWhereMultiMatch(['foo', 'bar'], 'baz', $options)->toDSL();

    assertArrayFragment(array_merge(['query' => 'baz', 'fields' => ['foo', 'bar']], $expected), $dsl);
})->with([
    'type as string' => [MatchType::CROSS_FIELDS, ['type' => MatchType::CROSS_FIELDS]],
    'type in options' => [MultiMatchOptions::make(MatchType::PHRASE), ['type' => MatchType::PHRASE]],
    'fuzziness' => [MultiMatchOptions::make(fuzziness: 'AUTO'), ['fuzziness' => 'AUTO']],
    'multiple options' => [
        MultiMatchOptions::make(type: MatchType::MOST_FIELDS, fuzziness: '3', minimumShouldMatch: '30%'),
        ['minimum_should_match' => '30%', 'fuzziness' => '3', 'type' => MatchType::MOST_FIELDS],
    ],
]);

test('bool query wildcard', function (?WildcardOptions $options, array $expected) {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()->whereWildcard('foo', '%value%', $options)->toDSL();

    assertArrayFragment(['must' => [['wildcard' => ['foo' => array_merge(['value' => '%value%'], $expected)]]]], $dsl);
})->with([
    'empty options' => [WildcardOptions::make(0, false), ['boost' => 0, 'case_insensitive' => false]],
    'full options' => [WildcardOptions::make(0.5, true), ['boost' => 0.5, 'case_insensitive' => true]],
    'rewrite options' => [WildcardOptions::make(rewrite: 'constant_score'), ['rewrite' => 'constant_score']],
]);

test('bool query or wildcard', function (?WildcardOptions $options, array $expected) {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()->orWhereWildcard('foo', '%value%', $options)->toDSL();

    assertArrayFragment(['should' => [['wildcard' => ['foo' => array_merge(['value' => '%value%'], $expected)]]]], $dsl);
})->with([
    'empty options' => [WildcardOptions::make(0, false), ['boost' => 0, 'case_insensitive' => false]],
    'full options' => [WildcardOptions::make(0.5, true), ['boost' => 0.5, 'case_insensitive' => true]],
    'rewrite options' => [WildcardOptions::make(rewrite: 'constant_score'), ['rewrite' => 'constant_score']],
]);

test('bool query prefix', function (Prefix $prefix, array $expected) {
    /** @var UnitTestCase $this */
    $dsl = BoolQueryBuilder::make()->prefix($prefix)->toDSL();

    assertArrayFragment(['must' => [['prefix' => $expected]]], $dsl);
})->with([
    'default' => [new Prefix(field: 'foo', value: 'bar'), ['foo' => ['value' => 'bar']]],
    'rewrite' => [
        new Prefix(field: 'foo', value: 'bar', rewrite: 'constant_score_blended'),
        ['foo' => ['value' => 'bar', 'rewrite' => 'constant_score_blended']],
    ],
    'case_insensitive' => [
        new Prefix(field: 'foo', value: 'bar', caseInsensitive: true),
        ['foo' => ['value' => 'bar', 'case_insensitive' => true]],
    ],
]);

test('bool query or prefix', function (Prefix $prefix, array $expected) {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()->orPrefix($prefix)->toDSL();

    assertArrayFragment(['should' => [['prefix' => $expected]]], $dsl);
})->with([
    'default' => [new Prefix(field: 'foo', value: 'bar'), ['foo' => ['value' => 'bar']]],
    'rewrite' => [
        new Prefix(field: 'foo', value: 'bar', rewrite: 'constant_score_blended'),
        ['foo' => ['value' => 'bar', 'rewrite' => 'constant_score_blended']],
    ],
    'case_insensitive' => [
        new Prefix(field: 'foo', value: 'bar', caseInsensitive: true),
        ['foo' => ['value' => 'bar', 'case_insensitive' => true]],
    ],
]);

test('bool query add must bool', function () {
    /** @var UnitTestCase $this */

    $builder = BoolQueryBuilder::make();
    $builder->where('mustName', 'value');
    $builder->addMustBool(fn (BoolQueryBuilder $builder) => $builder->orWhereWildcard('wildcardName', 'wildcardValue')->orWhereMatch('matchName', 'matchValue'));

    $dsl = $builder->toDSL();

    assertArrayFragment([
        'filter' => [["term" => ['mustName' => 'value']]],
        'must' => [["bool" => ['should' => [
            ['wildcard' => ['wildcardName' => ['value' => 'wildcardValue']]],
            ["match" => ['matchName' => ['query' => 'matchValue', 'operator' => 'or']]],
        ]]]],
    ], $dsl);
});

test('bool query dis_max in must', function () {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()
        ->whereDisMax(function ($dm) {
            $dm->match('name', 'foo', MatchOptions::make('and'));
            $dm->add(new Prefix(field: 'name.keyword', value: 'foo'));
        }, tieBreaker: 0.05)
        ->toDSL();

    assertArrayFragment([
        'bool' => [
            'must' => [[
               'dis_max' => [
                   'tie_breaker' => 0.05,
                   'queries' => [
                       ['match' => ['name' => ['query' => 'foo', 'operator' => 'and']]],
                       ['prefix' => ['name.keyword' => ['value' => 'foo']]],
                   ],
               ],
           ]],
        ],
    ], $dsl);
});

test('bool query dis_max in should', function () {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()
        ->orWhereDisMax(function ($dm) {
            $dm->match('name', 'foo');
        })
        ->toDSL();

    assertArrayFragment([
        'bool' => [
            'should' => [[
                'dis_max' => [
                    'queries' => [
                        ['match' => ['name' => ['query' => 'foo', 'operator' => 'or']]],
                    ],
                ],
            ]],
        ],
    ], $dsl);
});

test('bool query dis_max respects path', function () {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make('offers')
        ->whereDisMax(function ($dm) {
            $dm->match('seller_id', '10');
        })
        ->toDSL();

    assertArrayFragment(['match' => ['offers.seller_id' => ['operator' => 'or', 'query' => '10']]], $dsl);
});

test('bool query dis_max empty builder does nothing', function () {
    /** @var UnitTestCase $this */

    $dsl = BoolQueryBuilder::make()
        ->whereDisMax(fn () => null)
        ->toDSL();

    assertArrayFragment(['match_all' => new stdClass()], $dsl);
});
