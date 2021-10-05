<?php

namespace Ensi\LaravelElasticQuery\Filtering;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Illuminate\Support\Collection;

class CriteriaCollection implements DSLAware
{
    private Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function count(): int
    {
        return $this->items->count();
    }

    public function toDSL(): array
    {
        return $this->items
            ->map(fn (Criteria $criteria) => $criteria->toDSL())
            ->all();
    }

    public function add(Criteria $criteria): void
    {
        $this->items->push($criteria);
    }
}
