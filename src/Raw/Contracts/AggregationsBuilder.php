<?php

namespace Greensight\LaravelElasticQuery\Raw\Contracts;

use Closure;

interface AggregationsBuilder extends BoolQuery
{
    public function terms(string $name, string $field): static;

    public function minmax(string $name, string $field): static;

    public function nested(string $path, Closure $callback): static;
}
