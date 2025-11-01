<?php

namespace Ensi\LaravelElasticQuery;

use Elasticsearch\Client;
use Ensi\LaravelElasticQuery\Debug\QueryLogRecord;
use GuzzleHttp\Ring\Future\FutureArray;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Client getClient()
 * @method static array search(string $indexName, array $dsl, string|null $searchType = null)
 * @method static FutureArray searchAsync(string $indexName, array $dsl, string|null $searchType = null)
 * @method static array deleteByQuery(string $indexName, array $dsl)
 * @method static array termvectors(string $indexName, array $dsl)
 * @method static array get(string $indexName, int|string $id)
 * @method static array indicesExists(string $index)
 * @method static void indicesCreate(string $index, array $settings)
 * @method static array bulk(?string $index, array $body)
 * @method static array documentDelete(string $index, int|string $id)
 * @method static array catIndices(string $indexName, array|null $getFields = null)
 * @method static array indicesDelete(string $indexName, array $params = [])
 * @method static array indicesRefresh(string $indexName)
 * @method static array indicesReloadSearchAnalyzers(string $indexName)
 * @method static void enableQueryLog()
 * @method static void disableQueryLog()
 * @method static Collection|QueryLogRecord[] getQueryLog()
 */
class ElasticQuery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ElasticClient::class;
    }
}
