<?php

namespace Ensi\LaravelElasticQuery\Aggregating\Bucket;

use Ensi\LaravelElasticQuery\Aggregating\BucketCollection;
use Ensi\LaravelElasticQuery\Aggregating\Result;
use Ensi\LaravelElasticQuery\Contracts\Aggregation;
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
            fn (array $bucket) => Result::parseBucket($bucket),
            $response[$this->name]['buckets'] ?? []
        );

        return [$this->name => new BucketCollection($buckets)];
    }
}
