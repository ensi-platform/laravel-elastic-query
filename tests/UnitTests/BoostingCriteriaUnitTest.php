<?php

use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Boosting;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

use function PHPUnit\Framework\assertEquals;

uses(UnitTestCase::class);

test('boosting criteria builds dsl', function () {
    /** @var UnitTestCase $this */

    $positive = BoolQueryBuilder::make()->where('active', true);
    $negative = BoolQueryBuilder::make()
        ->where('locations.1.price', 0)
        ->whereNot('name.keyword', 'fruits')
        ->whereNot('vendor_code.keyword', 'fruits')
        ->whereNot('barcodes.keyword', 'fruits');

    $dsl = (new Boosting($positive, $negative, 0.0001))->toDSL();

    assertArrayStructure([
        'boosting' => ['positive', 'negative', 'negative_boost'],
    ], $dsl);

    assertArrayFragment($positive->toDSL(), $dsl['boosting']['positive']);
    assertArrayFragment($negative->toDSL(), $dsl['boosting']['negative']);
    assertEquals(0.0001, $dsl['boosting']['negative_boost']);
});

test('boosting criteria validates negative_boost', function () {
    $positive = BoolQueryBuilder::make()->where('active', true);
    $negative = BoolQueryBuilder::make()->where('locations.1.price', 0);

    new Boosting($positive, $negative, 0);
})->throws(InvalidArgumentException::class);

test('boosting criteria validates negative_boost upper bound', function () {
    $positive = BoolQueryBuilder::make()->where('active', true);
    $negative = BoolQueryBuilder::make()->where('locations.1.price', 0);

    new Boosting($positive, $negative, 1.01);
})->throws(InvalidArgumentException::class);
