<?php

namespace Ensi\LaravelElasticQuery;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Ensi\LaravelElasticQuery\Debug\QueryLog;
use Ensi\LaravelElasticQuery\Debug\QueryLogRecord;
use Illuminate\Support\Arr;
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

        return $this->client
            ->search(['index' => $indexName, 'body' => $dsl])
            ->asArray();
    }

    public function deleteByQuery(string $indexName, array $dsl): array
    {
        $this->queryLog?->log($indexName, $dsl);

        return $this->client
            ->deleteByQuery(['index' => $indexName, 'body' => $dsl])
            ->asArray();
    }

    public function get(string $indexName, int|string $id): array
    {
        return $this->client
            ->get(['index' => $indexName, 'id' => $id])
            ->asArray();
    }

    public function indicesExists(string $index): bool
    {
        return $this->client
            ->indices()
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
        return $this->client
            ->bulk(['index' => $index, 'body' => $body])
            ->asArray();
    }

    public function documentDelete(string $index, int|string $id): array
    {
        return $this->client
            ->delete(['index' => $index, 'id' => $id])
            ->asArray();
    }

    public function catIndices(string $indexName, ?array $getFields = null): array
    {
        $response = $this->client
            ->indices()
            ->stats(['index' => "$indexName*"])
            ->asArray();

        $results = [];
        foreach ($response['indices'] as $indexName => $stat) {
            $item = [
                'index' => $indexName,
                'health' => $stat['health'],
                'status' => $stat['status'],
                'uuid' => $stat['uuid'],
                'pri' => Arr::get($stat, 'primaries.shard_stats.total_count'),
                'rep' => Arr::get($stat, 'total.shard_stats.total_count'),
                'docs.count' => Arr::get($stat, 'total.docs.count'),
                'docs.deleted' => Arr::get($stat, 'total.docs.deleted'),
                'store.size' => Arr::get($stat, 'total.store.size_in_bytes'),
                'pri.store.size' => Arr::get($stat, 'primaries.store.size_in_bytes'),
            ];

            $results[] = !$getFields
                ? $item
                : Arr::only($item, $getFields);
        }

        return $results;
    }

    public function indicesDelete(string $indexName): array
    {
        return $this->client
            ->indices()
            ->delete(['index' => $indexName])
            ->asArray();
    }

    public function indicesRefresh(string $indexName): array
    {
        return $this->client
            ->indices()
            ->refresh(['index' => $indexName])
            ->asArray();
    }

    public function indicesReloadSearchAnalyzers(string $indexName): array
    {
        return $this->client
            ->indices()
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

        [$username, $password] = static::resolveBasicAuthData($config);

        if (filled($username)) {
            $builder->setBasicAuthentication($username, $password);
        }

        if (filled($config['handler'] ?? null)) {
            $builder->setHandler(call_user_func_array($config['handler'], []));
        }

        return new static($builder->build());
    }

    public static function resolveBasicAuthData(array $config): array
    {
        if (filled($config['username'] ?? null)) {
            return [$config['username'], $config['password'] ?? ''];
        }

        foreach ($config['hosts'] as $host) {
            $components = parse_url($host);

            if (filled($components['user'] ?? null)) {
                return [$components['user'], $components['pass'] ?? ''];
            }
        }

        return ['', ''];
    }
}
