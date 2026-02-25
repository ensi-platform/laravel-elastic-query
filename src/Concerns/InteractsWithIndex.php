<?php

namespace Ensi\LaravelElasticQuery\Concerns;

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\Analyzing\AnalyzeQuery;
use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuery\Suggesting\SuggestQuery;
use Exception;
use GuzzleHttp\Ring\Future\FutureArray;

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
    public function search(array $dsl, ?string $searchType = null): array
    {
        return $this->resolveClient()->search($this->indexName(), $dsl, $searchType);
    }

    /**
     * @see SearchIndex::searchAsync()
     */
    public function searchAsync(array $dsl, ?string $searchType = null): FutureArray
    {
        return $this->resolveClient()->searchAsync($this->indexName(), $dsl, $searchType);
    }

    /**
     * @see SearchIndex::search()
     */
    public function deleteByQuery(array $dsl): array
    {
        return $this->resolveClient()->deleteByQuery($this->indexName(), $dsl);
    }

    /**
     * @see SearchIndex::termvectors()
     */
    public function termvectors(array $dsl): array
    {
        return $this->resolveClient()->termvectors($this->indexName(), $dsl);
    }

    public function analyze(array $dsl): array
    {
        return $this->resolveClient()->analyze($this->indexName(), $dsl);
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

    public function catIndices(string $indexName, ?array $getFields = null): array
    {
        return $this->resolveClient()->catIndices($indexName, $getFields);
    }

    public function indicesDelete(string $index, array $params = []): array
    {
        return $this->resolveClient()->indicesDelete($index, $params);
    }

    public function indicesRefresh(): array
    {
        return $this->resolveClient()->indicesRefresh($this->indexName());
    }

    public function indicesReloadSearchAnalyzers(): array
    {
        return $this->resolveClient()->indicesReloadSearchAnalyzers($this->indexName());
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
