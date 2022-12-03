<?php

namespace Ensi\LaravelElasticQuery\Concerns;

use Closure;
use Ensi\LaravelElasticQuery\Aggregating\AggregationCollection;
use Ensi\LaravelElasticQuery\Aggregating\Bucket\NestedAggregation;
use Ensi\LaravelElasticQuery\Aggregating\Bucket\TermsAggregation;
use Ensi\LaravelElasticQuery\Aggregating\CompositeAggregationBuilder;
use Ensi\LaravelElasticQuery\Aggregating\Metrics\MinMaxAggregation;
use Ensi\LaravelElasticQuery\Aggregating\Metrics\ValueCountAggregation;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;

trait ConstructsAggregations
{
    use SupportsPath;
    use DecoratesBoolQuery;

    protected AggregationCollection $aggregations;
    protected BoolQueryBuilder $boolQuery;

    public function terms(string $name, string $field, ?int $size = null): static
    {
        $this->aggregations->add(new TermsAggregation($name, $this->absolutePath($field), $size));

        return $this;
    }

    public function minmax(string $name, string $field): static
    {
        $this->aggregations->add(new MinMaxAggregation($name, $this->absolutePath($field)));

        return $this;
    }

    public function count(string $name, string $field): static
    {
        $this->aggregations->add(new ValueCountAggregation($name, $this->absolutePath($field)));

        return $this;
    }

    public function nested(string $path, Closure $callback): static
    {
        $name = $this->aggregations->generateUniqueName($this->name());
        $builder = $this->createCompositeBuilder("{$name}_filter", $path);

        /** @var AggregationCollection $aggs */
        $aggs = tap($builder, $callback)->build();

        if (!$aggs->isEmpty()) {
            $nested = new NestedAggregation($name, $path, $aggs);
            $this->aggregations->merge(AggregationCollection::fromAggregation($nested));
        }

        return $this;
    }

    protected function name(): string
    {
        return '';
    }

    protected function boolQuery(): BoolQueryBuilder
    {
        return $this->boolQuery;
    }

    protected function createCompositeBuilder(?string $name = null, string $path = ''): CompositeAggregationBuilder
    {
        return new CompositeAggregationBuilder(
            $name ?? $this->aggregations->generateUniqueName(),
            $this->absolutePath($path)
        );
    }
}
