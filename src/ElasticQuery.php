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
 * @method static array|Promise bulk(?string $index, array $body, array $params = [])
 * @method static array safeBulk(?string $index, iterable $body, int $chunkSize = 500, array $params = [])
 * @method static array|Promise documentDelete(string $index, int|string $id)
 * @method static array|Promise catIndices(string $indexName, array|null $getFields = null)
 * @method static array|Promise indicesInfo(array|null $indices = [], array $columns = ['i'], array $sort = [], string|null $health = null)
 * @method static array|Promise indicesDelete(string $indexName, array $params = [])
 * @method static array|Promise indicesRefresh(string $indexName)
 * @method static array|Promise indicesReloadSearchAnalyzers(string $indexName)
 * @method static array|Promise analyze(string $indexName, array $dsl)
 * @method static array|Promise getSynonymsSets(int|null $from = null, int|null $size = null)
 * @method static array|Promise getSynonymSet(string $id)
 * @method static array|Promise putSynonymSet(string $id, array $synonymsSet)
 * @method static array|Promise deleteSynonymSet(string $id)
 * @method static array|Promise getSynonymRule(string $setId, string $ruleId)
 * @method static array|Promise putSynonymRule(string $setId, string $ruleId, string $synonyms)
 * @method static array|Promise deleteSynonymRule(string $setId, string $ruleId)
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
