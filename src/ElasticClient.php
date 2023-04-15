<?php

namespace Ensi\LaravelElasticQuery;

use Ensi\LaravelElasticQuery\Contracts\ClientAdapter;
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

        return $this->adapter->search([
            'index' => $indexName,
            'body' => $dsl,
        ]);
    }

    public function deleteByQuery(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->adapter->deleteByQuery([
            'index' => $indexName,
            'body' => $dsl,
        ]);
    }

    public function get(string $indexName, int|string $id): array
    {
        return $this->adapter->get([
            'index' => $indexName,
            'id' => $id,
        ]);
    }

    public function indicesExists(string $index): bool
    {
        return $this->adapter->indicesExists(['index' => $index]);
    }

    public function indicesCreate(string $index, array $settings): void
    {
        $this->adapter->indicesCreate([
            'index' => $index,
            'body' => $settings,
        ]);
    }

    public function bulk(string $index, array $body): array
    {
        return $this->adapter->bulk([
            'index' => $index,
            'body' => $body,
        ]);
    }

    public function documentDelete(string $index, int|string $id): array
    {
        return $this->adapter->documentDelete([
            'index' => $index,
            'id' => $id,
        ]);
    }

    public function catIndices(string $indexName, ?array $getFields = null): array
    {
        $params = ['index' => "$indexName*"];
        if ($getFields) {
            $params['h'] = $getFields;
        }

        return $this->adapter->catIndices($params);
    }

    public function indicesDelete(string $indexName): array
    {
        return $this->adapter->indicesDelete(['index' => $indexName]);
    }

    public function indicesRefresh(string $indexName): array
    {
        return $this->adapter->indicesRefresh(['index' => $indexName]);
    }

    public function indicesReloadSearchAnalyzers(string $indexName): array
    {
        return $this->adapter->indicesReloadSearchAnalyzers(['index' => $indexName]);
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
