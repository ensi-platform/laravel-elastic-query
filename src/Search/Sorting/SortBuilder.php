<?php

namespace Ensi\LaravelElasticQuery\Search\Sorting;

use Closure;
use Ensi\LaravelElasticQuery\Concerns\DecoratesBoolQuery;
use Ensi\LaravelElasticQuery\Concerns\ExtendsSort;
use Ensi\LaravelElasticQuery\Concerns\SupportsPath;
use Ensi\LaravelElasticQuery\Contracts\SortableQuery;
use Ensi\LaravelElasticQuery\Contracts\SortOrder;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Illuminate\Support\Collection;

class SortBuilder implements SortableQuery
{
    use DecoratesBoolQuery;
    use ExtendsSort;
    use SupportsPath;

    private SortCollection $sorts;
    private Collection $levels;

    public function __construct(SortCollection $sorts)
    {
        $this->sorts = $sorts;
        $this->levels = new Collection();
    }

    public function sortBy(string $field, string $order = SortOrder::ASC, ?string $mode = null): static
    {
        $path = $this->absolutePath($field);

        $sort = new Sort(
            $path,
            strtolower($order),
            $mode === null ? $mode : strtolower($mode),
            $this->buildNested()
        );

        $this->sorts->add($sort);

        return $this;
    }

    public function sortByNested(string $field, Closure $callback): static
    {
        $path = $this->absolutePath($field);
        $filter = new BoolQueryBuilder($path, false);

        $this->levels->prepend($filter, $path);

        try {
            $callback($this);
        } finally {
            $this->levels->shift();
        }

        return $this;
    }

    protected function boolQuery(): BoolQueryBuilder
    {
        return $this->levels->first();
    }

    protected function basePath(): string
    {
        return $this->levels->isNotEmpty() ? $this->levels->keys()[0] : '';
    }

    private function buildNested(): ?NestedSort
    {
        if ($this->levels->isEmpty()) {
            return null;
        }

        return $this->levels->reduce(function (?NestedSort $carry, BoolQueryBuilder $query, string $path) {
            return new NestedSort($path, $query, $carry);
        });
    }
}
