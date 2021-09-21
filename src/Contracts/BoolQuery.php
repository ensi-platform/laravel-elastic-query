<?php

namespace Greensight\LaravelElasticQuery\Contracts;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

interface BoolQuery
{
    public function where(string $field, mixed $operator, mixed $value = null): static;

    public function whereNot(string $field, mixed $value): static;

    public function whereIn(string $field, array|Arrayable $values): static;

    public function whereNotIn(string $field, array|Arrayable $values): static;

    public function whereHas(string $nested, Closure $filter): static;

    public function whereDoesntHave(string $nested, Closure $filter): static;

    public function whereNull(string $field): static;

    public function whereNotNull(string $field): static;
}
