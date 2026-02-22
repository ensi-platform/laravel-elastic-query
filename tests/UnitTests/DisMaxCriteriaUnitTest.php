<?php

use Ensi\LaravelElasticQuery\Contracts\MatchOptions;
use Ensi\LaravelElasticQuery\Filtering\Criterias\DisMax;
use Ensi\LaravelElasticQuery\Filtering\Criterias\OneMatch;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Prefix;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('dis_max criteria serializes', function () {
    $disMax = new DisMax(
        queries: [
            new OneMatch('name', 'foo', MatchOptions::make('and')),
            new Prefix(field: 'name.keyword', value: 'foo'),
        ],
        tieBreaker: 0.1,
        boost: 3.0,
    );

    $dsl = $disMax->toDSL();

    assertArrayFragment([
        'dis_max' => [
            'tie_breaker' => 0.1,
            'boost' => 3.0,
            'queries' => [
                ['match' => ['name' => ['query' => 'foo', 'operator' => 'and']]],
                ['prefix' => ['name.keyword' => ['value' => 'foo']]],
            ],
        ],
    ], $dsl);
});
