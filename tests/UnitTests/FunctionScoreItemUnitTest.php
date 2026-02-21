<?php

use Ensi\LaravelElasticQuery\Contracts\FunctionScoreItem;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Terms;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('function score item serializes float weight', function () {
    $item = new FunctionScoreItem(
        0.7,
        new Terms('category_ids', ['EXAMPLE_CAT_ID'])
    );

    $dsl = $item->toArray();

    expect($dsl['weight'])->toBe(0.7);

    assertArrayFragment(
        ['filter' => ['terms' => ['category_ids' => ['EXAMPLE_CAT_ID']]]],
        $dsl
    );
});
