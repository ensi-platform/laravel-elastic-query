<?php

use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Tests\Data\Models\CapturingProductsIndex;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('search query supports min_score', function () {
    /** @var UnitTestCase $this */

    $index = new CapturingProductsIndex();
    $query = new SearchQuery($index);

    $query->minScore(1)->where('active', true)->get();

    expect($index->lastDsl['min_score'])->toBe(1.0);
});
