<?php

namespace Ensi\LaravelElasticQuery\Concerns;

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Exception;

trait InteractsWithIndex
{
    private ?ElasticClient $client = null;

    /**
     * @see SearchIndex::tiebreaker()
     */
    abstract public function tiebreaker(): string;

    abstract protected function indexName(): string;

    protected function settings(): array
    {
        throw new Exception("Необходимо переопределить метод");
    }

    /**
     * @see SearchIndex::search()
     */
    public function search(array $dsl): array
    {
        return $this->resolveClient()->search($this->indexName(), $dsl);
    }

    public function isCreated(): bool
    {
        return $this->resolveClient()->indicesExists($this->indexName());
    }

    public function create(): void
    {
        $this->resolveClient()->indicesCreate($this->indexName(), $this->settings());
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
