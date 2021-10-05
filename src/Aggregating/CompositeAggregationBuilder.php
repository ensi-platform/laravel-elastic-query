<?php

namespace Ensi\LaravelElasticQuery\Aggregating;

use Ensi\LaravelElasticQuery\Aggregating\Bucket\FilterAggregation;
use Ensi\LaravelElasticQuery\Concerns\ConstructsAggregations;
use Ensi\LaravelElasticQuery\Contracts\AggregationsBuilder;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;

class CompositeAggregationBuilder implements AggregationsBuilder
{
    use ConstructsAggregations;

    public function __construct(protected string $name, protected string $path = '')
    {
        $this->boolQuery = new BoolQueryBuilder($path);
        $this->aggregations = new AggregationCollection();
    }

    public function build(): AggregationCollection
    {
        if ($this->aggregations->isEmpty() || $this->boolQuery->isEmpty()) {
            return $this->aggregations;
        }

        $filter = new FilterAggregation($this->name, $this->boolQuery, $this->aggregations);

        return AggregationCollection::fromAggregation($filter);
    }

    public function toDSL(): array
    {
        return $this->build()->toDSL();
    }

    protected function basePath(): string
    {
        return $this->path;
    }

    protected function name(): string
    {
        return $this->name;
    }
}
