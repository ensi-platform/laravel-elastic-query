<?php

namespace Greensight\LaravelElasticQuery\Debug;

use Carbon\CarbonInterface;

class QueryLogRecord
{
    public function __construct(
        public string $indexName,
        public array $query,
        public CarbonInterface $timestamp
    ) {
    }
}
