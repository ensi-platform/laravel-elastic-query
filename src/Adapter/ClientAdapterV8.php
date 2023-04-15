<?php

namespace Ensi\LaravelElasticQuery\Adapter;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Ensi\LaravelElasticQuery\Contracts\ClientAdapter;

class ClientAdapterV8 implements ClientAdapter
{
    private Client $client;

    public function __construct(ClientConfig $config)
    {
        $this->client = (new ClientBuilder())
            ->setHosts($config->getHosts())
            ->setRetries($config->getRetries())
            ->setBasicAuthentication($config->getUsername(), $config->getPassword())
            ->setSSLVerification($config->getSSLVerification())
            ->build();
    }

    public function search(array $params): array
    {
        return $this->client->search($params)->asArray();
    }

    public function deleteByQuery(array $params): array
    {
        return $this->client->deleteByQuery($params)->asArray();
    }

    public function get(array $params): array
    {
        return $this->client->get($params)->asArray();
    }

    public function indicesExists(array $params): bool
    {
        return $this->client->indices()
            ->exists($params)
            ->asBool();
    }

    public function indicesCreate(array $params): void
    {
        $this->client->indices()->create($params);
    }

    public function bulk(array $params): array
    {
        return $this->client->bulk($params)->asArray();
    }

    public function documentDelete(array $params): array
    {
        return $this->client->delete($params)->asArray();
    }

    public function catIndices(array $params): array
    {
        return $this->client->cat()
            ->indices($params)
            ->asArray();
    }

    public function indicesDelete(array $params): array
    {
        return $this->client->indices()
            ->delete($params)
            ->asArray();
    }

    public function indicesRefresh(array $params): array
    {
        return $this->client->indices()
            ->refresh($params)
            ->asArray();
    }

    public function indicesReloadSearchAnalyzers(array $params): array
    {
        return $this->client->indices()
            ->reloadSearchAnalyzers($params)
            ->asArray();
    }
}
