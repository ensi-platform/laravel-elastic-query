<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Http\Promise\Promise;

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
     * @return array|Promise
     */
    public function search(array $dsl, ?string $searchType = null): array|Promise;

    /**
     * Perform delete by query.
     *
     * @param array $dsl
     * @return array|Promise
     */
    public function deleteByQuery(array $dsl): array|Promise;

    /**
     * Retrieves information and statistics for terms in the fields of a particular document.
     *
     * @param array $dsl
     * @return array|Promise
     */
    public function termvectors(array $dsl): array|Promise;

    /**
     * Analyze text using analyzer/tokenizer/filters in index scope.
     *
     * @param array $dsl
     * @return array|Promise
     */
    public function analyze(array $dsl): array|Promise;
}
