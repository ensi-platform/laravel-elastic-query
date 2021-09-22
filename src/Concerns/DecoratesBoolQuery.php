<?php

namespace Greensight\LaravelElasticQuery\Concerns;

use Closure;
use Greensight\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\ForwardsCalls;

trait DecoratesBoolQuery
{
    use ForwardsCalls;

    abstract protected function boolQuery(): BoolQueryBuilder;

    public function where(string $field, mixed $operator, mixed $value = null): static
    {
        $this->forwardCallTo($this->boolQuery(), __FUNCTION__, func_get_args());

        return $this;
    }

    public function whereNot(string $field, mixed $value): static
    {
        $this->forwardCallTo($this->boolQuery(), __FUNCTION__, func_get_args());

        return $this;
    }

    public function whereHas(string $nested, Closure $filter): static
    {
        $this->forwardCallTo($this->boolQuery(), __FUNCTION__, func_get_args());

        return $this;
    }

    public function whereDoesntHave(string $nested, Closure $filter): static
    {
        $this->forwardCallTo($this->boolQuery(), __FUNCTION__, func_get_args());

        return $this;
    }

    public function whereIn(string $field, array|Arrayable $values): static
    {
        $this->forwardCallTo($this->boolQuery(), __FUNCTION__, func_get_args());

        return $this;
    }

    public function whereNotIn(string $field, array|Arrayable $values): static
    {
        $this->forwardCallTo($this->boolQuery(), __FUNCTION__, func_get_args());

        return $this;
    }

    public function whereNull(string $field): static
    {
        $this->forwardCallTo($this->boolQuery(), __FUNCTION__, func_get_args());

        return $this;
    }

    public function whereNotNull(string $field): static
    {
        $this->forwardCallTo($this->boolQuery(), __FUNCTION__, func_get_args());

        return $this;
    }
}
