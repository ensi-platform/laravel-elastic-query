<?php

use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Tests\Data\Models\CapturingProductsIndex;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

use function PHPUnit\Framework\assertEquals;

uses(UnitTestCase::class);


test('search query uses bool query by default', function () {
    /** @var UnitTestCase $this */

    $index = new CapturingProductsIndex();
    $query = new SearchQuery($index);

    $query->where('active', true)->get();

    assertArrayFragment(
        ['query' => ['term' => ['active' => true]]],
        $index->lastDsl
    );
});

test('search query wraps root query with boosting', function () {
    /** @var UnitTestCase $this */

    $index = new CapturingProductsIndex();
    $query = new SearchQuery($index);

    $query->where('active', true);

    $negative = BoolQueryBuilder::make()
        ->where('locations.1.price', 0)
        ->whereNot('name.keyword', 'ка');

    $query->boosting($negative, 0.0001)->get();

    assertArrayStructure([
        'query' => [
            'boosting' => ['positive', 'negative', 'negative_boost'],
        ],
    ], $index->lastDsl);

    assertArrayFragment(
        ['term' => ['active' => true]],
        $index->lastDsl['query']['boosting']['positive']
    );

    assertArrayFragment(
        $negative->toDSL(),
        $index->lastDsl['query']['boosting']['negative']
    );

    assertEquals(0.0001, $index->lastDsl['query']['boosting']['negative_boost']);
});
