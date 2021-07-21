<?php

namespace Greensight\LaravelElasticQuery\Raw\Aggregating;

use Greensight\LaravelElasticQuery\Raw\Aggregating\Bucket\FilterAggregation;
use Greensight\LaravelElasticQuery\Raw\Concerns\ConstructsAggregations;
use Greensight\LaravelElasticQuery\Raw\Contracts\AggregationsBuilder;
use Greensight\LaravelElasticQuery\Raw\Filtering\BoolQueryBuilder;

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
