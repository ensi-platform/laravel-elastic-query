<?php

namespace Ensi\LaravelElasticQuery;

use Elastic\Elasticsearch\Client;
use Ensi\LaravelElasticQuery\Debug\QueryLogRecord;
use Http\Promise\Promise;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Client getClient()
 * @method static array|Promise search(string $indexName, array $dsl, string|null $searchType = null)
 * @method static array|Promise deleteByQuery(string $indexName, array $dsl)
 * @method static array|Promise termvectors(string $indexName, array $dsl)
 * @method static array|Promise get(string $indexName, int|string $id)
 * @method static array|Promise indicesExists(string $index)
 * @method static null|Promise indicesCreate(string $index, array $settings)
 * @method static array|Promise bulk(?string $index, array $body)
 * @method static array|Promise documentDelete(string $index, int|string $id)
 * @method static array|Promise catIndices(string $indexName, array|null $getFields = null)
 * @method static array|Promise indicesInfo(array|null $indices = [], array $columns = ['i'], array $sort = [], string|null $health = null)
 * @method static array|Promise indicesDelete(string $indexName, array $params = [])
 * @method static array|Promise indicesRefresh(string $indexName)
 * @method static array|Promise indicesReloadSearchAnalyzers(string $indexName)
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
