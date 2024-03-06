<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Closure;
use Ensi\LaravelElasticQuery\Scripts\Script;

interface SortableQuery extends BoolQuery
{
    public function sortBy(string $field, string $order = SortOrder::ASC, ?string $mode = null, ?string $missingValues = null): static;

    public function minSortBy(string $field, string $order = SortOrder::ASC): static;

    public function maxSortBy(string $field, string $order = SortOrder::ASC): static;

    public function avgSortBy(string $field, string $order = SortOrder::ASC): static;

    public function sumSortBy(string $field, string $order = SortOrder::ASC): static;

    public function medianSortBy(string $field, string $order = SortOrder::ASC): static;

    public function sortByNested(string $field, Closure $callback): static;

    public function sortByScript(Script $script, string $type = ScriptSortType::NUMBER, string $order = SortOrder::ASC): static;

    public function sortByCustomArray(string $field, array $items): static;
}
