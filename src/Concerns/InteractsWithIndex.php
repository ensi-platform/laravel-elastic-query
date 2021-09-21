<?php

namespace Greensight\LaravelElasticQuery\Concerns;

use Greensight\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Greensight\LaravelElasticQuery\Contracts\SearchIndex;
use Greensight\LaravelElasticQuery\ElasticClient;
use Greensight\LaravelElasticQuery\Search\SearchQuery;

trait InteractsWithIndex
{
    private ?ElasticClient $client = null;

    /**
     * @see SearchIndex::tiebreaker()
     */
    abstract public function tiebreaker(): string;

    abstract protected function indexName(): string;

    /**
     * @see SearchIndex::search()
     */
    public function search(array $dsl): array
    {
        return $this->resolveClient()->search($this->indexName(), $dsl);
    }

    public static function query(): SearchQuery
    {
        return new SearchQuery(new static());
    }

    public static function aggregate(): AggregationsQuery
    {
        return new AggregationsQuery(new static());
    }

    protected function resolveClient(): ElasticClient
    {
        $this->client ??= resolve(ElasticClient::class);

        return $this->client;
    }
}
