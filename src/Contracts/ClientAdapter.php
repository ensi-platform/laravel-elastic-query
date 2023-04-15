<?php

namespace Ensi\LaravelElasticQuery\Contracts;

interface ClientAdapter
{
    public function search(array $params): array;

    public function get(array $params): array;

    public function documentDelete(array $params): array;

    public function deleteByQuery(array $params): array;

    public function bulk(array $params): array;

    public function indicesCreate(array $params): void;

    public function indicesReloadSearchAnalyzers(array $params): array;

    public function indicesExists(array $params): bool;

    public function indicesRefresh(array $params): array;

    public function catIndices(array $params): array;

    public function indicesDelete(array $params): array;
}
