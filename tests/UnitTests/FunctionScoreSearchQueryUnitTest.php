<?php

use Ensi\LaravelElasticQuery\Contracts\FunctionScoreItem;
use Ensi\LaravelElasticQuery\Contracts\FunctionScoreOptions;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Terms;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Tests\Data\Models\CapturingProductsIndex;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('search query wraps root query with function_score', function () {
    $index = new CapturingProductsIndex();
    $query = new SearchQuery($index);

    $query->where('active', true);

    $query->functionScore(
        functions: [
            new FunctionScoreItem(4.0, new Terms('category_ids', ['BOOST_CAT_ID'])),
        ],
        options: FunctionScoreOptions::make(scoreMode: 'max', boostMode: 'multiply')
    );

    $query->get();

    assertArrayStructure([
        'query' => [
            'function_score' => ['query', 'functions', 'score_mode', 'boost_mode'],
        ],
    ], $index->lastDsl);

    assertArrayFragment(
        ['term' => ['active' => true]],
        $index->lastDsl['query']['function_score']['query']
    );
});
