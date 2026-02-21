<?php

namespace Ensi\LaravelElasticQuery\Aggregating;

use Closure;
use Ensi\LaravelElasticQuery\Concerns\ConstructsAggregations;
use Ensi\LaravelElasticQuery\Contracts\AggregationsBuilder;
use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Ensi\LaravelElasticQuery\Contracts\FunctionScoreOptions;
use Ensi\LaravelElasticQuery\Contracts\FunctionScoreScript;
use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Ensi\LaravelElasticQuery\Filtering\Criterias\FunctionScore;
use Ensi\LaravelElasticQuery\Response;
use Http\Promise\Promise;
use Illuminate\Support\Collection;

class AggregationsQuery implements AggregationsBuilder
{
    use ConstructsAggregations;

    protected DSLAware $rootQuery;

    public function __construct(protected SearchIndex $index)
    {
        $this->aggregations = new AggregationCollection();
        $this->boolQuery = new BoolQueryBuilder();

        $this->rootQuery = $this->boolQuery;
    }

    public function composite(Closure $callback): static
    {
        /** @var AggregationCollection $aggs */
        $aggs = tap($this->createCompositeBuilder(), $callback)->build();

        $this->aggregations->merge($aggs);

        return $this;
    }

    public function functionScore(
        array $functions,
        ?FunctionScoreOptions $options = null,
        ?FunctionScoreScript $scriptScore = null,
        ?float $weight = null,
    ): static {
        $this->rootQuery = new FunctionScore(
            query: $this->rootQuery,
            options: $options,
            functions: $functions,
            scriptScore: $scriptScore,
            weight: $weight,
        );

        return $this;
    }

    public function disableFunctionScore(): static
    {
        $this->rootQuery = $this->boolQuery;

        return $this;
    }

    public function get(): Collection|Promise
    {
        if ($this->aggregations->isEmpty()) {
            return new Collection();
        }

        return Response::fn(
            $this->execute(),
            function (array $response) {
                return $this->aggregations->parseResults($response['aggregations'] ?? []);
            }
        );
    }

    protected function execute(): array|Promise
    {
        $dsl = [
            'size' => 0,
            'track_total_hits' => false,
            'query' => $this->rootQuery->toDSL(),
            'aggs' => $this->aggregations->toDSL(),
        ];

        return $this->index->search($dsl);
    }
}
