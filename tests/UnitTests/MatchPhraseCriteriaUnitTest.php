<?php

use Ensi\LaravelElasticQuery\Contracts\MatchPhraseOptions;
use Ensi\LaravelElasticQuery\Filtering\Criterias\MatchPhrase;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('match_phrase criteria serializes', function (?MatchPhraseOptions $options, ?float $boost, array $expected) {
    /** @var UnitTestCase $this */

    $criteria = new MatchPhrase(field: 'name', query: 'foo', options: $options, boost: $boost);

    $dsl = $criteria->toDSL();

    assertArrayFragment([
        'match_phrase' => [
            'name' => array_merge(['query' => 'foo'], $expected),
        ],
    ], $dsl);
})->with([
    'no options' => [null, null, []],
    'slop' => [MatchPhraseOptions::make(slop: 2), null, ['slop' => 2]],
    'analyzer' => [MatchPhraseOptions::make(analyzer: 'synonym_analyzer'), null, ['analyzer' => 'synonym_analyzer']],
    'zero_terms_query' => [MatchPhraseOptions::make(zeroTermsQuery: 'all'), null, ['zero_terms_query' => 'all']],
    'boost' => [null, 1.5, ['boost' => 1.5]],
    'many' => [
        MatchPhraseOptions::make(slop: 1, analyzer: 'synonym_analyzer', zeroTermsQuery: 'none'),
        2.0,
        ['slop' => 1, 'analyzer' => 'synonym_analyzer', 'zero_terms_query' => 'none', 'boost' => 2.0],
    ],
]);
