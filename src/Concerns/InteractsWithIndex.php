<?php

namespace Ensi\LaravelElasticQuery\Concerns;

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\Analyzing\AnalyzeQuery;
use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Suggesting\SuggestQuery;
use Exception;
use Http\Promise\Promise;

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
    public function search(array $dsl, ?string $searchType = null): array|Promise
    {
        return $this->resolveClient()->search($this->indexName(), $dsl, $searchType);
    }

    /**
     * @see SearchIndex::search()
     */
    public function deleteByQuery(array $dsl): array|Promise
    {
        return $this->resolveClient()->deleteByQuery($this->indexName(), $dsl);
    }

    /**
     * @see SearchIndex::termvectors()
     */
    public function termvectors(array $dsl): array|Promise
    {
        return $this->resolveClient()->termvectors($this->indexName(), $dsl);
    }

    public function isCreated(): bool|Promise
    {
        return $this->resolveClient()->indicesExists($this->indexName());
    }

    public function create(): ?Promise
    {
        return $this->resolveClient()->indicesCreate($this->indexName(), $this->settings());
    }

    public function bulk(array $body, array $params = []): array|Promise
    {
        return $this->resolveClient()->bulk($this->indexName(), $body, $params);
    }

    public function safeBulk(iterable $body, int $chunkSize = 500, array $params = []): array
    {
        return $this->resolveClient()->safeBulk($this->indexName(), $body, $chunkSize, $params);
    }

    public function get(int|string $id): array|Promise
    {
        return $this->resolveClient()->get($this->indexName(), $id);
    }

    public function documentDelete(int|string $id): array|Promise
    {
        return $this->resolveClient()->documentDelete($this->indexName(), $id);
    }

    public function catIndices(string $indexName, ?array $getFields = null): array|Promise
    {
        return $this->resolveClient()->catIndices($indexName, $getFields);
    }

    public function indicesInfo(array $columns = ['i'], array $sort = [], ?string $health = null): array|Promise
    {
        return $this->resolveClient()->indicesInfo(
            indices: [$this->indexName()],
            columns: $columns,
            sort: $sort,
            health: $health
        );
    }

    public function indicesDelete(string $index, array $params = []): array|Promise
    {
        return $this->resolveClient()->indicesDelete($index, $params);
    }

    public function indicesRefresh(): array|Promise
    {
        return $this->resolveClient()->indicesRefresh($this->indexName());
    }

    public function indicesReloadSearchAnalyzers(): array|Promise
    {
        return $this->resolveClient()->indicesReloadSearchAnalyzers($this->indexName());
    }

    public function analyze(array $dsl): array|Promise
    {
        return $this->resolveClient()->analyze($this->indexName(), $dsl);
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

    public static function analyzeText(): AnalyzeQuery
    {
        return new AnalyzeQuery(new static());
    }

    protected function resolveClient(): ElasticClient
    {
        $this->client ??= resolve(ElasticClient::class);

        return $this->client;
    }
}
