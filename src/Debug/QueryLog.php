<?php

namespace Ensi\LaravelElasticQuery\Debug;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class QueryLog
{
    private Collection $records;

    public function __construct()
    {
        $this->records = new Collection();
    }

    public function log(string $indexName, array $query): void
    {
        // Here we use timestamp to disable influence of setTestNow
        $timestamp = Date::createFromTimestamp(microtime());

        $record = new QueryLogRecord($indexName, $query, $timestamp);

        $this->records->push($record);
    }

    public function all(): Collection
    {
        return $this->records;
    }
}
