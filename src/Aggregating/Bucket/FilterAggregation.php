<?php

namespace Greensight\LaravelElasticQuery\Aggregating\Bucket;

use Greensight\LaravelElasticQuery\Aggregating\AggregationCollection;
use Greensight\LaravelElasticQuery\Contracts\Aggregation;
use Greensight\LaravelElasticQuery\Contracts\Criteria;
use Webmozart\Assert\Assert;

class FilterAggregation implements Aggregation
{
    public function __construct(
        private string $name,
        private Criteria $criteria,
        private AggregationCollection $children
    ) {
        Assert::stringNotEmpty(trim($name));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toDSL(): array
    {
        return [$this->name => [
            'filter' => $this->criteria->toDSL(),
            'aggs' => $this->children->toDSL(),
        ]];
    }

    public function parseResults(array $response): array
    {
        return $this->children
            ->parseResults($response[$this->name] ?? [])
            ->all();
    }
}
