<?php

use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Tests\Data\Models\CapturingProductsIndex;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('search query forwards whereDisMax to bool query', function () {
    /** @var UnitTestCase $this */

    $index = new CapturingProductsIndex();
    $query = new SearchQuery($index);

    $query->whereDisMax(function ($dm) {
        $dm->match('name', 'foo');
    }, tieBreaker: 0.05)->get();

    assertArrayFragment([
        'query' => [
            'dis_max' => [
                'tie_breaker' => 0.05,
            ],
        ],
    ], $index->lastDsl['query']['bool']['must'][0]);
});
