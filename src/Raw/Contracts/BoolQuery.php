<?php

namespace Greensight\LaravelElasticQuery\Raw\Contracts;

use Closure;

interface BoolQuery
{
    public function where(string $field, mixed $operator, mixed $value = null): static;

    public function whereNot(string $field, mixed $value): static;

    public function whereHas(string $nested, Closure $filter): static;

    public function whereDoesntHave(string $nested, Closure $filter): static;
}
