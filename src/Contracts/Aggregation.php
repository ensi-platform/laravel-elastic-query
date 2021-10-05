<?php

namespace Ensi\LaravelElasticQuery\Contracts;

interface Aggregation extends DSLAware
{
    public function name(): string;

    public function parseResults(array $response): array;
}
