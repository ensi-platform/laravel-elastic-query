<?php

namespace Greensight\LaravelElasticQuery\Concerns;

use Greensight\LaravelElasticQuery\Contracts\SortMode;
use Greensight\LaravelElasticQuery\Contracts\SortOrder;

/**
 * @psalm-require-implements \Greensight\LaravelElasticQuery\Contracts\SortableQuery
 *
 * @method static sortBy(string $field, string $order = SortOrder::ASC, ?string $mode = null)
 */
trait ExtendsSort
{
    public function minSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::MIN);
    }

    public function maxSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::MAX);
    }

    public function avgSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::AVG);
    }

    public function sumSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::SUM);
    }

    public function medianSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::MEDIAN);
    }
}
