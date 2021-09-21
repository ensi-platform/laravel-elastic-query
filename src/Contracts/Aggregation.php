<?php

namespace Greensight\LaravelElasticQuery\Contracts;

interface Aggregation extends DSLAware
{
    public function name(): string;

    public function parseResults(array $response): array;
}
