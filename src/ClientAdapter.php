<?php

namespace Ensi\LaravelElasticQuery;

interface ClientAdapter
{
    public function search(string $indexName, array $dsl): array;

    public function catIndices(string $indexName, ?array $getFields = null): array;

    public function indicesCreate(string $index, array $settings): void;

    public function get(string $indexName, int|string $id): array;

    public function indicesReloadSearchAnalyzers(string $indexName): array;

    public function documentDelete(string $index, int|string $id): array;

    public function indicesExists(string $index): bool;

    public function indicesRefresh(string $indexName): array;

    public function deleteByQuery(string $indexName, array $dsl): array;

    public function bulk(string $index, array $body): array;

    public function indicesDelete(string $indexName): array;
}
