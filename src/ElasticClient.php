<?php

namespace Ensi\LaravelElasticQuery;

use Ensi\LaravelElasticQuery\Debug\QueryLog;
use Ensi\LaravelElasticQuery\Debug\QueryLogRecord;
use Illuminate\Support\Collection;

class ElasticClient
{
    private ?QueryLog $queryLog = null;

    public function __construct(private ClientAdapter $adapter)
    {
    }

    public function search(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->adapter->search($indexName, $dsl);
    }

    public function deleteByQuery(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->adapter->deleteByQuery($indexName, $dsl);
    }

    public function get(string $indexName, int|string $id): array
    {
        return $this->adapter->get($indexName, $id);
    }

    public function indicesExists(string $index): bool
    {
        return $this->adapter->indicesExists($index);
    }

    public function indicesCreate(string $index, array $settings): void
    {
        $this->adapter->indicesCreate($index, $settings);
    }

    public function bulk(string $index, array $body): array
    {
        return $this->adapter->bulk($index, $body);
    }

    public function documentDelete(string $index, int|string $id): array
    {
        return $this->adapter->documentDelete($index, $id);
    }

    public function catIndices(string $indexName, ?array $getFields = null): array
    {
        return $this->adapter->catIndices($indexName, $getFields);
    }

    public function indicesDelete(string $indexName): array
    {
        return $this->adapter->indicesDelete($indexName);
    }

    public function indicesRefresh(string $indexName): array
    {
        return $this->adapter->indicesRefresh($indexName);
    }

    public function indicesReloadSearchAnalyzers(string $indexName): array
    {
        return $this->adapter->indicesReloadSearchAnalyzers($indexName);
    }

    public function enableQueryLog(): void
    {
        $this->queryLog ??= new QueryLog();
    }

    public function disableQueryLog(): void
    {
        $this->queryLog = null;
    }

    /**
     * @return Collection|QueryLogRecord[]
     */
    public function getQueryLog(): Collection
    {
        return $this->queryLog?->all() ?? new Collection();
    }
}
