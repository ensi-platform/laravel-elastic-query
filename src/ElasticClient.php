<?php

namespace Ensi\LaravelElasticQuery;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Ensi\LaravelElasticQuery\Debug\QueryLog;
use Ensi\LaravelElasticQuery\Debug\QueryLogRecord;
use GuzzleHttp\Ring\Future\FutureArray;
use Illuminate\Support\Collection;

class ElasticClient
{
    private ?QueryLog $queryLog = null;

    public function __construct(private Client $client)
    {
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function search(string $indexName, array $dsl, ?string $searchType = null): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->client->search(array_filter([
            'index' => $indexName,
            'body' => $dsl,
            'search_type' => $searchType,
        ]));
    }

    public function searchAsync(string $indexName, array $dsl, ?string $searchType = null): FutureArray
    {
        $this->queryLog?->log($indexName, $dsl);

        /** @var FutureArray $result */
        $result = $this->client->search($this->paramsAsync(array_filter([
            'index' => $indexName,
            'body' => $dsl,
            'search_type' => $searchType,
        ])));

        return $result;
    }

    public function deleteByQuery(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->client->deleteByQuery([
            'index' => $indexName,
            'body' => $dsl,
        ]);
    }

    public function termvectors(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->client->termvectors([
            'index' => $indexName,
            'body' => $dsl,
        ]);
    }

    public function analyze(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->client->indices()->analyze([
            'index' => $indexName,
            'body' => $dsl,
        ]);
    }

    public function get(string $indexName, int|string $id): array
    {
        return $this->client->get([
            'index' => $indexName,
            'id' => $id,
        ]);
    }

    public function indicesExists(string $index): bool
    {
        return $this->client->indices()->exists(['index' => $index]);
    }

    public function indicesCreate(string $index, array $settings): void
    {
        $this->client->indices()->create([
            'index' => $index,
            'body' => $settings,
        ]);
    }

    public function bulk(?string $index, array $body): array
    {
        return $this->client->bulk(array_filter([
            'index' => $index,
            'body' => $body,
        ]));
    }

    public function documentDelete(string $index, int|string $id): array
    {
        return $this->client->delete([
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

        return $this->client->cat()->indices($params);
    }

    public function indicesDelete(string $indexName, array $params = []): array
    {
        return $this->client->indices()->delete(array_merge($params, ['index' => $indexName]));
    }

    public function indicesRefresh(string $indexName): array
    {
        return $this->client->indices()->refresh(['index' => $indexName]);
    }

    public function indicesReloadSearchAnalyzers(string $indexName): array
    {
        return $this->client->indices()->reloadSearchAnalyzers(['index' => $indexName]);
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
     * @return Collection<int,QueryLogRecord>
     */
    public function getQueryLog(): Collection
    {
        return $this->queryLog?->all() ?? new Collection();
    }

    public static function fromConfig(array $config): static
    {
        $builder = (new ClientBuilder())
            ->setHosts($config['hosts'])
            ->setRetries($config['retries'] ?? 1)
            ->setSSLVerification($config['ssl_verification'] ?? false);

        if (filled($config['username'] ?? null)) {
            $builder->setBasicAuthentication($config['username'], $config['password'] ?? '');
        }

        if (filled($config['handler'] ?? null)) {
            $builder->setHandler(call_user_func_array($config['handler'], []));
        }

        return new static($builder->build());
    }

    protected function paramsAsync(array $params): array
    {
        return array_merge_recursive($params, [
            'client' => ['future' => 'lazy'],
        ]);
    }
}
