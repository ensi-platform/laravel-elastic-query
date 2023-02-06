<?php

namespace Ensi\LaravelElasticQuery\Concerns;

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Suggesting\SuggestQuery;
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
        throw new Exception("Need to redefine the method");
    }

    /**
     * @see SearchIndex::search()
     */
    public function search(array $dsl): array
    {
        return $this->resolveClient()->search($this->indexName(), $dsl);
    }

    /**
     * @see SearchIndex::search()
     */
    public function deleteByQuery(array $dsl): array
    {
        return $this->resolveClient()->deleteByQuery($this->indexName(), $dsl);
    }

    public function isCreated(): bool
    {
        return $this->resolveClient()->indicesExists($this->indexName());
    }

    public function create(): void
    {
        $this->resolveClient()->indicesCreate($this->indexName(), $this->settings());
    }

    public function bulk(array $body): array
    {
        return $this->resolveClient()->bulk($this->indexName(), $body);
    }

    public function get(int|string $id): array
    {
        return $this->resolveClient()->get($this->indexName(), $id);
    }

    public function documentDelete(int|string $id): array
    {
        return $this->resolveClient()->documentDelete($this->indexName(), $id);
    }

    public function catIndices(string $indexName, ?array $getFields = null)
    {
        return $this->resolveClient()->catIndices($indexName, $getFields);
    }

    public function indicesDelete(string $index)
    {
        return $this->resolveClient()->indicesDelete($index);
    }

    public function indicesRefresh()
    {
        return $this->resolveClient()->indicesRefresh($this->indexName());
    }

    public static function query(): SearchQuery
    {
        return new SearchQuery(new static());
    }

    public static function aggregate(): AggregationsQuery
    {
        return new AggregationsQuery(new static());
    }

    public static function suggest(): SuggestQuery
    {
        return new SuggestQuery(new static());
    }

    protected function resolveClient(): ElasticClient
    {
        $this->client ??= resolve(ElasticClient::class);

        return $this->client;
    }
}
