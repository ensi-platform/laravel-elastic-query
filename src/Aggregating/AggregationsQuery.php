<?php

namespace Greensight\LaravelElasticQuery\Aggregating;

use Closure;
use Greensight\LaravelElasticQuery\Concerns\ConstructsAggregations;
use Greensight\LaravelElasticQuery\Contracts\AggregationsBuilder;
use Greensight\LaravelElasticQuery\Contracts\SearchIndex;
use Greensight\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Illuminate\Support\Collection;

class AggregationsQuery implements AggregationsBuilder
{
    use ConstructsAggregations;

    public function __construct(protected SearchIndex $index)
    {
        $this->aggregations = new AggregationCollection();
        $this->boolQuery = new BoolQueryBuilder();
    }

    public function composite(Closure $callback): static
    {
        /** @var AggregationCollection $aggs */
        $aggs = tap($this->createCompositeBuilder(), $callback)->build();

        $this->aggregations->merge($aggs);

        return $this;
    }

    public function get(): Collection
    {
        if ($this->aggregations->isEmpty()) {
            return new Collection();
        }

        $response = $this->execute();

        return $this->aggregations->parseResults($response['aggregations'] ?? []);
    }

    protected function execute(): array
    {
        $dsl = [
            'size' => 0,
            'track_total_hits' => false,
            'query' => $this->boolQuery->toDSL(),
            'aggs' => $this->aggregations->toDSL(),
        ];

        return $this->index->search($dsl);
    }
}
