<?php

namespace Ensi\LaravelElasticQuery;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Ensi\LaravelElasticQuery\Debug\QueryLog;
use Ensi\LaravelElasticQuery\Debug\QueryLogRecord;
use Http\Promise\Promise;
use Illuminate\Support\Arr;
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

    public function search(string $indexName, array $dsl, ?string $searchType = null): array|Promise
    {
        $this->queryLog?->log($indexName, $dsl);

        return Response::array(
            $this->client->search(array_filter([
                'index' => $indexName,
                'body' => $dsl,
                'search_type' => $searchType,
            ]))
        );
    }

    public function termvectors(string $indexName, array $dsl): array|Promise
    {
        $this->queryLog?->log($indexName, $dsl);

        return Response::array(
            $this->client->termvectors([
                'index' => $indexName,
                'body' => $dsl,
            ])
        );
    }

    public function deleteByQuery(string $indexName, array $dsl): array|Promise
    {
        $this->queryLog?->log($indexName, $dsl);

        return Response::array(
            $this->client->deleteByQuery(['index' => $indexName, 'body' => $dsl])
        );
    }

    public function get(string $indexName, int|string $id): array|Promise
    {
        return Response::array(
            $this->client->get(['index' => $indexName, 'id' => $id])
        );
    }

    public function indicesExists(string $index): bool|Promise
    {
        return Response::bool(
            $this->client->indices()->exists(['index' => $index])
        );
    }

    public function indicesCreate(string $index, array $settings): ?Promise
    {
        return Response::void(
            $this->client->indices()->create([
                'index' => $index,
                'body' => $settings,
            ])
        );
    }

    public function bulk(?string $index, array $body): array|Promise
    {
        return Response::array(
            $this->client->bulk(array_filter(['index' => $index, 'body' => $body]))
        );
    }

    public function documentDelete(string $index, int|string $id): array|Promise
    {
        return Response::array(
            $this->client->delete(['index' => $index, 'id' => $id])
        );
    }

    public function catIndices(string $indexName, ?array $getFields = null): array
    {
        return Response::fn(
            $this->client->indices()->stats(['index' => "{$indexName}*"]),
            function (Elasticsearch $response) use ($getFields) {
                $response = $response->asArray();

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
        );
    }

    public function indicesInfo(
        ?array $indices = [],
        array $columns = ['i'],
        array $sort = [],
        ?string $health = null
    ): array|Promise {
        $params = ['format' => 'json', 'h' => 'i'];
        if ($indices) {
            $params['index'] = implode(',', $indices);
        }
        if ($health) {
            $params['h'] = $health;
        }
        if ($columns) {
            $params['h'] = implode(',', $columns);
        }
        if ($sort) {
            $params['s'] = implode(',', $sort);
        }

        return Response::array(
            $this->client->cat()->indices($params)
        );
    }

    public function indicesDelete(string $indexName): array|Promise
    {
        return Response::array(
            $this->client->indices()->delete(['index' => $indexName])
        );
    }

    public function indicesRefresh(string $indexName): array|Promise
    {
        return Response::array(
            $this->client->indices()->refresh(['index' => $indexName])
        );
    }

    public function indicesReloadSearchAnalyzers(string $indexName): array|Promise
    {
        return Response::array(
            $this->client->indices()->reloadSearchAnalyzers(['index' => $indexName])
        );
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

        if (filled($config['api_key']) ?? null) {
            $builder->setApiKey($config['api_key']);
        }

        if (filled($config['http_client_logger'] ?? null)) {
            $logger = call_user_func_array($config['http_client_logger'], []);

            if (!is_null($logger)) {
                $builder->setLogger($logger);
            }
        }

        if (filled($config['http_client'] ?? null)) {
            $client = new $config['http_client']();

            $builder->setHttpClient($client);
        }

        if (filled($config['http_client_options'] ?? null)) {
            $options = call_user_func_array($config['http_client_options'], []);

            $builder->setHttpClientOptions($options);
        }

        if (filled($config['http_async_client'] ?? null)) {
            $builder->setAsyncHttpClient(call_user_func_array($config['http_async_client'], []));
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
