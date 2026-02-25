<?php

use Ensi\LaravelElasticQuery\Contracts\MatchPhrasePrefixOptions;
use Ensi\LaravelElasticQuery\Filtering\Criterias\MatchPhrasePrefix;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('match_phrase_prefix criteria serializes', function (?MatchPhrasePrefixOptions $options, ?float $boost, array $expected) {
    /** @var UnitTestCase $this */

    $criteria = new MatchPhrasePrefix(field: 'name', query: 'foo', options: $options, boost: $boost);

    $dsl = $criteria->toDSL();

    assertArrayFragment([
        'match_phrase_prefix' => [
            'name' => array_merge(['query' => 'foo'], $expected),
        ],
    ], $dsl);
})->with([
    'no options' => [null, null, []],
    'slop' => [MatchPhrasePrefixOptions::make(slop: 3), null, ['slop' => 3]],
    'max_expansions' => [MatchPhrasePrefixOptions::make(maxExpansions: 50), null, ['max_expansions' => 50]],
    'analyzer' => [MatchPhrasePrefixOptions::make(analyzer: 'synonym_analyzer'), null, ['analyzer' => 'synonym_analyzer']],
    'zero_terms_query' => [MatchPhrasePrefixOptions::make(zeroTermsQuery: 'all'), null, ['zero_terms_query' => 'all']],
    'boost' => [null, 1.2, ['boost' => 1.2]],
    'many' => [
        MatchPhrasePrefixOptions::make(slop: 1, maxExpansions: 10, analyzer: 'synonym_analyzer', zeroTermsQuery: 'none'),
        2.5,
        ['slop' => 1, 'max_expansions' => 10, 'analyzer' => 'synonym_analyzer', 'zero_terms_query' => 'none', 'boost' => 2.5],
    ],
]);
