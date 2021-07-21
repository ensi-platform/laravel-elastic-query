<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Raw\Aggregating\Buckets;

use Greensight\LaravelElasticQuery\Raw\Aggregating\Bucket;
use Greensight\LaravelElasticQuery\Raw\Aggregating\Bucket\TermsAggregation;
use Greensight\LaravelElasticQuery\Raw\Aggregating\BucketCollection;
use Greensight\LaravelElasticQuery\Tests\AssertsArray;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class TermsAggregationTest extends UnitTestCase
{
    use AssertsArray;

    public function testToDSL(): void
    {
        $testing = new TermsAggregation('agg1', 'code');

        $this->assertArrayStructure(['agg1' => ['terms' => ['field']]], $testing->toDSL());
    }

    public function testParseResults(): void
    {
        $result = $this->executeParseResults('agg1');

        $this->assertArrayHasKey('agg1', $result);
    }

    public function testParseResultsReturnsCollection(): void
    {
        $result = $this->executeParseResults('agg1');

        $this->assertInstanceOf(BucketCollection::class, $result['agg1']);
    }

    public function testParseResultsReadsBuckets(): void
    {
        $result = $this->executeParseResults('agg1');

        $this->assertInstanceOf(Bucket::class, $result['agg1']->first());
    }

    public function testParseEmptyResults(): void
    {
        $result = $this->executeParseResults('agg1', []);

        $this->assertArrayHasKey('agg1', $result);
        $this->assertInstanceOf(BucketCollection::class, $result['agg1']);
    }

    private function executeParseResults(string $aggName, ?array $buckets = null): array
    {
        if ($buckets === null) {
            $buckets = [['key' => 'tv', 'doc_count' => 4]];
        }

        $response = [$aggName => [
            'doc_count_error_upper_bound' => 0,
            'buckets' => $buckets,
        ]];

        $testing = new TermsAggregation($aggName, 'code');

        return $testing->parseResults($response);
    }
}
