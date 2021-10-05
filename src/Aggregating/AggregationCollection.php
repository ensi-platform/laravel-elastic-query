<?php

namespace Ensi\LaravelElasticQuery\Aggregating;

use Ensi\LaravelElasticQuery\Contracts\Aggregation;
use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class AggregationCollection implements DSLAware
{
    private Collection $aggregations;

    public function __construct()
    {
        $this->aggregations = new Collection();
    }

    public static function fromAggregation(Aggregation $aggregation): static
    {
        $instance = new static();
        $instance->add($aggregation);

        return $instance;
    }

    public function add(Aggregation $aggregation): void
    {
        if ($this->aggregations->has($aggregation->name())) {
            throw new InvalidArgumentException("Aggregation {$aggregation->name()} already exists in collection");
        }

        $this->aggregations->put($aggregation->name(), $aggregation);
    }

    public function isEmpty(): bool
    {
        return $this->aggregations->isEmpty();
    }

    public function count(): int
    {
        return $this->aggregations->count();
    }

    public function merge(AggregationCollection $source): void
    {
        $source->aggregations->each(fn (Aggregation $agg) => $this->add($agg));
    }

    public function toDSL(): array
    {
        return $this->aggregations
            ->mapWithKeys(fn (Aggregation $aggregation) => $aggregation->toDSL())
            ->all();
    }

    public function parseResults(array $response): Collection
    {
        return $this->aggregations
            ->mapWithKeys(fn (Aggregation $agg) => $agg->parseResults($response));
    }

    public function generateUniqueName(string $name = ''): string
    {
        $salt = $this->aggregations->count() + 1;

        return blank($name) ? "agg_$salt" : "{$name}_$salt";
    }
}
