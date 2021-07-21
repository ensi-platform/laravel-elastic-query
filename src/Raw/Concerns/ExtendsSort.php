<?php

namespace Greensight\LaravelElasticQuery\Raw\Concerns;

/**
 * @psalm-require-implements \Greensight\LaravelElasticQuery\Raw\Contracts\SortableQuery
 *
 * @method static sortBy(string $field, string $order = 'asc', ?string $mode = null)
 */
trait ExtendsSort
{
    public function minSortBy(string $field, string $order = 'asc'): static
    {
        return $this->sortBy($field, $order, 'min');
    }

    public function maxSortBy(string $field, string $order = 'asc'): static
    {
        return $this->sortBy($field, $order, 'max');
    }

    public function avgSortBy(string $field, string $order = 'asc'): static
    {
        return $this->sortBy($field, $order, 'avg');
    }

    public function sumSortBy(string $field, string $order = 'asc'): static
    {
        return $this->sortBy($field, $order, 'sum');
    }

    public function medianSortBy(string $field, string $order = 'asc'): static
    {
        return $this->sortBy($field, $order, 'median');
    }
}
