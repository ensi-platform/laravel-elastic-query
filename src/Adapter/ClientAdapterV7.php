<?php

namespace Ensi\LaravelElasticQuery\Adapter;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Ensi\LaravelElasticQuery\Contracts\ClientAdapter;

class ClientAdapterV7 implements ClientAdapter
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

    public function search(string $indexName, array $dsl): array
    {
        return $this->client->search([
            'index' => $indexName,
            'body' => $dsl,
        ]);
    }

    public function deleteByQuery(string $indexName, array $dsl): array
    {
        return $this->client->deleteByQuery([
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

    public function bulk(string $index, array $body): array
    {
        return $this->client->bulk([
            'index' => $index,
            'body' => $body,
        ]);
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

    public function indicesDelete(string $indexName): array
    {
        return $this->client->indices()->delete(['index' => $indexName]);
    }

    public function indicesRefresh(string $indexName): array
    {
        return $this->client->indices()->refresh(['index' => $indexName]);
    }

    public function indicesReloadSearchAnalyzers(string $indexName): array
    {
        return $this->client->indices()->reloadSearchAnalyzers(['index' => $indexName]);
    }
}
