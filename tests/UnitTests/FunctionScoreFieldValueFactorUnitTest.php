<?php

use Ensi\LaravelElasticQuery\Contracts\BoostMode;
use Ensi\LaravelElasticQuery\Contracts\FunctionScoreFieldValueFactor;
use Ensi\LaravelElasticQuery\Contracts\FunctionScoreItem;
use Ensi\LaravelElasticQuery\Contracts\FunctionScoreOptions;
use Ensi\LaravelElasticQuery\Contracts\ScoreMode;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Terms;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Tests\Data\Models\CapturingProductsIndex;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

use function PHPUnit\Framework\assertEquals;

uses(UnitTestCase::class);

test('field value factor function score item builds array', function () {
    $dsl = (new FunctionScoreFieldValueFactor(
        field: 'weight',
        factor: 1.5,
        modifier: 'sqrt',
        missing: 1,
        weight: 2.0,
        filter: new Terms('full_category_ids', ['685']),
    ))->toArray();

    assertArrayStructure([
        'field_value_factor' => ['field', 'factor', 'modifier', 'missing'],
        'filter' => ['terms'],
        'weight',
    ], $dsl);

    assertEquals([
        'field_value_factor' => [
            'field' => 'weight',
            'factor' => 1.5,
            'modifier' => 'sqrt',
            'missing' => 1,
        ],
        'filter' => [
            'terms' => [
                'full_category_ids' => ['685'],
            ],
        ],
        'weight' => 2.0,
    ], $dsl);
});

test('field value factor function score item omits null options', function () {
    $dsl = (new FunctionScoreFieldValueFactor(
        field: 'weight',
        missing: 1,
    ))->toArray();

    assertEquals([
        'field_value_factor' => [
            'field' => 'weight',
            'missing' => 1,
        ],
    ], $dsl);
});

test('field value factor validates modifier', function () {
    new FunctionScoreFieldValueFactor(
        field: 'weight',
        modifier: 'invalid_modifier',
    );
})->throws(InvalidArgumentException::class);

test('search query supports nested function score with field value factor', function () {
    $index = new CapturingProductsIndex();
    $query = new SearchQuery($index);

    $query->where('active', true);

    $query->functionScore(
        functions: [
            new FunctionScoreItem(
                weight: 4.0,
                filter: new Terms('full_category_ids', ['685']),
            ),
        ],
        options: FunctionScoreOptions::make(
            scoreMode: ScoreMode::MAX,
            boostMode: BoostMode::MULTIPLY,
        ),
    );

    $query->functionScore(
        functions: [
            new FunctionScoreFieldValueFactor(
                field: 'weight',
                missing: 1,
            ),
        ],
        options: FunctionScoreOptions::make(
            boostMode: BoostMode::MULTIPLY,
        ),
    )->get();

    assertArrayStructure([
        'query' => [
            'function_score' => ['query', 'functions', 'boost_mode'],
        ],
    ], $index->lastDsl);

    $outer = $index->lastDsl['query']['function_score'];

    assertEquals(BoostMode::MULTIPLY, $outer['boost_mode']);
    assertEquals([
        [
            'field_value_factor' => [
                'field' => 'weight',
                'missing' => 1,
            ],
        ],
    ], $outer['functions']);

    assertArrayStructure([
        'function_score' => ['query', 'functions', 'score_mode', 'boost_mode'],
    ], $outer['query']);

    $inner = $outer['query']['function_score'];

    assertEquals(ScoreMode::MAX, $inner['score_mode']);
    assertEquals(BoostMode::MULTIPLY, $inner['boost_mode']);

    assertArrayFragment(
        ['term' => ['active' => true]],
        $inner['query']
    );

    assertEquals([
        [
            'filter' => [
                'terms' => [
                    'full_category_ids' => ['685'],
                ],
            ],
            'weight' => 4.0,
        ],
    ], $inner['functions']);
});
