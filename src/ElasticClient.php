<?php

namespace Ensi\LaravelElasticQuery;

use Elastic\Elasticsearch\Client;
use Ensi\LaravelElasticQuery\Debug\QueryLog;
use Ensi\LaravelElasticQuery\Debug\QueryLogRecord;
use Illuminate\Support\Collection;

class ElasticClient
{
    private ?QueryLog $queryLog = null;

    public function __construct(private Client $client)
    {
    }

    public function search(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->client->search(['index' => $indexName, 'body' => $dsl])
            ->asArray();
    }

    public function deleteByQuery(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->client->deleteByQuery(['index' => $indexName, 'body' => $dsl])
            ->asArray();
    }

    public function get(string $indexName, int|string $id): array
    {
        return $this->client->get(['index' => $indexName, 'id' => $id])
            ->asArray();
    }

    public function indicesExists(string $index): bool
    {
        return $this->client->indices()
            ->exists(['index' => $index])
            ->asBool();
    }

    public function indicesCreate(string $index, array $settings): void
    {
        $this->client->indices()->create([
            'index' => $index,
            'body' => $settings,
        ]);
    }

    public function bulk(string $index, array $body): array
    {
        return $this->client->bulk(['index' => $index, 'body' => $body])
            ->asArray();
    }

    public function documentDelete(string $index, int|string $id): array
    {
        return $this->client->delete(['index' => $index, 'id' => $id])
            ->asArray();
    }

    public function catIndices(string $indexName, ?array $getFields = null): array
    {
        $params = ['index' => "$indexName*"];
        if ($getFields) {
            $params['h'] = $getFields;
        }

        return $this->client->cat()
            ->indices($params)
            ->asArray();
    }

    public function indicesDelete(string $indexName): array
    {
        return $this->client->indices()
            ->delete(['index' => $indexName])
            ->asArray();
    }

    public function indicesRefresh(string $indexName): array
    {
        return $this->client->indices()
            ->refresh(['index' => $indexName])
            ->asArray();
    }

    public function indicesReloadSearchAnalyzers(string $indexName): array
    {
        return $this->client->indices()
            ->reloadSearchAnalyzers(['index' => $indexName])
            ->asArray();
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
