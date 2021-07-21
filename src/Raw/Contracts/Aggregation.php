<?php

namespace Greensight\LaravelElasticQuery\Raw\Contracts;

interface Aggregation extends DSLAware
{
    public function name(): string;

    public function parseResults(array $response): array;
}
