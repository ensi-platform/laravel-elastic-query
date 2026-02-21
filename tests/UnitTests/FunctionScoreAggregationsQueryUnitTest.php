<?php

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\Contracts\FunctionScoreItem;
use Ensi\LaravelElasticQuery\Contracts\FunctionScoreOptions;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Terms;
use Ensi\LaravelElasticQuery\Tests\Data\Models\CapturingProductsIndex;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('aggregations query wraps root query with function_score', function () {
    $index = new CapturingProductsIndex();
    $query = new AggregationsQuery($index);

    $query->terms('codes', 'code');
    $query->where('active', true);

    $query->functionScore(
        functions: [
            new FunctionScoreItem(4.0, new Terms('category_ids', ['BOOST_CAT_ID'])),
            new FunctionScoreItem(0.7, new Terms('category_ids', ['NEGATIVE_CAT_ID'])),
        ],
        options: FunctionScoreOptions::make(
            scoreMode: 'max',
            boostMode: 'multiply',
        )
    );

    $query->get();

    assertArrayStructure([
        'query' => [
            'function_score' => [
                'query',
                'functions',
                'score_mode',
                'boost_mode',
            ],
        ],
    ], $index->lastDsl);

    assertArrayFragment(
        ['term' => ['active' => true]],
        $index->lastDsl['query']['function_score']['query']
    );

    expect($index->lastDsl['query']['function_score']['functions'][0]['weight'])->toBe(4.0);
    expect($index->lastDsl['query']['function_score']['functions'][1]['weight'])->toBe(0.7);

    assertArrayFragment(
        ['terms' => ['category_ids' => ['BOOST_CAT_ID']]],
        $index->lastDsl['query']['function_score']['functions'][0]['filter']
    );
});
