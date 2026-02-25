<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use GuzzleHttp\Ring\Future\FutureArray;

interface SearchIndex
{
    /**
     * Returns the name of attribute with unique values in index scope.
     *
     * @return string
     */
    public function tiebreaker(): string;

    /**
     * Perform search query.
     *
     * @param array $dsl
     * @param string|null $searchType
     * @return array
     */
    public function search(array $dsl, ?string $searchType = null): array;

    /**
     * Perform search query async.
     *
     * @param array $dsl
     * @param string|null $searchType
     * @return FutureArray
     */
    public function searchAsync(array $dsl, ?string $searchType = null): FutureArray;

    /**
     * Perform delete by query.
     *
     * @param array $dsl
     * @return array
     */
    public function deleteByQuery(array $dsl): array;

    /**
     * Retrieves information and statistics for terms in the fields of a particular document.
     *
     * @param array $dsl
     * @return array
     */
    public function termvectors(array $dsl): array;

    /**
     * POST /{index}/_analyze
     *
     * @param array $dsl
     * @return array
     */
    public function analyze(array $dsl): array;
}
