<?php

namespace Greensight\LaravelElasticQuery\Aggregating;

use Greensight\LaravelElasticQuery\Aggregating\Bucket\FilterAggregation;
use Greensight\LaravelElasticQuery\Concerns\ConstructsAggregations;
use Greensight\LaravelElasticQuery\Contracts\AggregationsBuilder;
use Greensight\LaravelElasticQuery\Filtering\BoolQueryBuilder;

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
