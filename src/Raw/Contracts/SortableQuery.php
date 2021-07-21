<?php

namespace Greensight\LaravelElasticQuery\Raw\Contracts;

use Closure;

interface SortableQuery extends BoolQuery
{
    public function sortBy(string $field, string $order = 'asc', ?string $mode = null): static;

    public function minSortBy(string $field, string $order = 'asc'): static;

    public function maxSortBy(string $field, string $order = 'asc'): static;

    public function avgSortBy(string $field, string $order = 'asc'): static;

    public function sumSortBy(string $field, string $order = 'asc'): static;

    public function medianSortBy(string $field, string $order = 'asc'): static;

    public function sortByNested(string $field, Closure $callback): static;
}
