<?php

namespace Greensight\LaravelElasticQuery;

use Greensight\LaravelElasticQuery\Raw\Debug\QueryLogRecord;
use Greensight\LaravelElasticQuery\Raw\ElasticClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array search(string $indexName, array $dsl)
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
