<?php

namespace Ensi\LaravelElasticQuery\Aggregating\Bucket;

use Ensi\LaravelElasticQuery\Aggregating\BucketCollection;
use Ensi\LaravelElasticQuery\Aggregating\Result;
use Ensi\LaravelElasticQuery\Contracts\Aggregation;
use Webmozart\Assert\Assert;

class TermsAggregation implements Aggregation
{
    public function __construct(private string $name, private string $field, private ?int $size = null)
    {
        Assert::stringNotEmpty(trim($name));
        Assert::stringNotEmpty(trim($field));
        Assert::nullOrGreaterThan($this->size, 0);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toDSL(): array
    {
        $body = ['field' => $this->field];

        if ($this->size !== null) {
            $body['size'] = $this->size;
        }

        return [
            $this->name => [
                'terms' => $body,
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
