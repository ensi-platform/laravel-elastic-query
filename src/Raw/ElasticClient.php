<?php

namespace Greensight\LaravelElasticQuery\Raw;

use Elasticsearch\Client;
use Greensight\LaravelElasticQuery\Raw\Debug\QueryLog;
use Greensight\LaravelElasticQuery\Raw\Debug\QueryLogRecord;
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

        return $this->client->search([
            'index' => $indexName,
            'body' => $dsl,
        ]);
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
