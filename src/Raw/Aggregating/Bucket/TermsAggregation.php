<?php

namespace Greensight\LaravelElasticQuery\Raw\Aggregating\Bucket;

use Greensight\LaravelElasticQuery\Raw\Aggregating\Bucket;
use Greensight\LaravelElasticQuery\Raw\Aggregating\BucketCollection;
use Greensight\LaravelElasticQuery\Raw\Contracts\Aggregation;
use Webmozart\Assert\Assert;

class TermsAggregation implements Aggregation
{
    public function __construct(private string $name, private string $field)
    {
        Assert::stringNotEmpty(trim($name));
        Assert::stringNotEmpty(trim($field));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toDSL(): array
    {
        return [
            $this->name => [
                'terms' => [
                    'field' => $this->field,
                ],
            ],
        ];
    }

    public function parseResults(array $response): array
    {
        $buckets = array_map(
            fn (array $bucket) => new Bucket($bucket['key'], (int)$bucket['doc_count']),
            $response[$this->name]['buckets'] ?? []
        );

        return [$this->name => new BucketCollection($buckets)];
    }
}
