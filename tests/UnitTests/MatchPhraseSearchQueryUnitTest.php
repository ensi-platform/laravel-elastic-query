<?php

use Ensi\LaravelElasticQuery\Contracts\MatchPhrasePrefixOptions;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Tests\Data\Models\CapturingProductsIndex;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('search query forwards whereMatchPhrasePrefix to bool query', function () {
    /** @var UnitTestCase $this */

    $index = new CapturingProductsIndex();
    $query = new SearchQuery($index);

    $query->whereMatchPhrasePrefix('name', 'bana', MatchPhrasePrefixOptions::make(maxExpansions: 10), 2.0)->get();

    assertArrayFragment([
        'match_phrase_prefix' => [
            'name' => [
                'query' => 'bana',
                'max_expansions' => 10,
                'boost' => 2.0,
            ],
        ],
    ], $index->lastDsl['query']['bool']['must'][0]);
});
