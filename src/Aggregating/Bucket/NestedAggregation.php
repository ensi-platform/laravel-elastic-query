<?php

namespace Ensi\LaravelElasticQuery\Aggregating\Bucket;

use Ensi\LaravelElasticQuery\Aggregating\AggregationCollection;
use Ensi\LaravelElasticQuery\Contracts\Aggregation;
use Webmozart\Assert\Assert;

class NestedAggregation implements Aggregation
{
    public function __construct(private string $name, private string $path, private AggregationCollection $children)
    {
        Assert::stringNotEmpty(trim($name));
        Assert::stringNotEmpty(trim($path));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toDSL(): array
    {
        return [$this->name => [
            'nested' => ['path' => $this->path],
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
