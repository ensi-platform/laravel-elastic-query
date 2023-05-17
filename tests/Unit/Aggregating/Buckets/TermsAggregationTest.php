<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit\Aggregating\Buckets;

use Ensi\LaravelElasticQuery\Aggregating\Bucket;
use Ensi\LaravelElasticQuery\Aggregating\Bucket\TermsAggregation;
use Ensi\LaravelElasticQuery\Aggregating\BucketCollection;
use Ensi\LaravelElasticQuery\Aggregating\Metrics\MinMaxScoreAggregation;
use Ensi\LaravelElasticQuery\Aggregating\MinMax;
use Ensi\LaravelElasticQuery\Search\Sorting\Sort;
use Ensi\LaravelElasticQuery\Tests\AssertsArray;
use Ensi\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class TermsAggregationTest extends UnitTestCase
{
    use AssertsArray;

    public function testToDSL(): void
    {
        $testing = new TermsAggregation('agg1', 'code');

        $this->assertArrayStructure(['agg1' => ['terms' => ['field']]], $testing->toDSL());
    }

    public function testToDSLWithSize(): void
    {
        $testing = new TermsAggregation('agg1', 'code', 24);

        $this->assertArrayStructure(['agg1' => ['terms' => ['field', 'size']]], $testing->toDSL());
    }

    public function testToDSLWithSort(): void
    {
        $orderField = 'name';
        $sort = new Sort($orderField);

        $testing = new TermsAggregation(
            name: 'agg1',
            field: 'code',
            sort: $sort
        );

        $this->assertArrayStructure(['agg1' => ['terms' => ['field', 'order' => [$orderField]]]], $testing->toDSL());
    }

    public function testToDSLWithComposite(): void
    {
        $composite = new MinMaxScoreAggregation();

        $testing = new TermsAggregation(
            name: 'agg1',
            field: 'code',
            composite: $composite
        );

        $this->assertArrayStructure(['agg1' => ['terms' => ['field'], 'aggs' => [
            'score_min' => ['min' => ['script']],
            'score_max' => ['max' => ['script']],
        ]]], $testing->toDSL());
    }

    public function testToDSLWithAll(): void
    {
        $orderField = 'name';
        $sort = new Sort($orderField);
        $composite = new MinMaxScoreAggregation();

        $testing = new TermsAggregation(
            name: 'agg1',
            field: 'code',
            size: 24,
            sort: $sort,
            composite: $composite
        );

        $this->assertArrayStructure([
            'agg1' => ['terms' => ['field', 'size', 'order' => [$orderField]],
            'aggs' => [
                'score_min' => ['min' => ['script']],
                'score_max' => ['max' => ['script']],
            ]]], $testing->toDSL());
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

    public function testParseResultsReadsBucketsWithComposite(): void
    {
        $buckets = [
            [
                'key' => 'tv',
                'doc_count' => 4,
                'score_max' => ['value' => 2],
                'score_min' => ['value' => 1],
            ],
        ];

        $result = $this->executeParseResultsWithComposite('agg1', $buckets);

        /** @var Bucket $bucket */
        $bucket = $result['agg1']->first();
        /** @var MinMax $score */
        $score = $bucket->getCompositeValue('score');

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertEquals($buckets[0]['score_min']['value'], $score->min);
        $this->assertEquals($buckets[0]['score_max']['value'], $score->max);
    }

    public function testParseEmptyResults(): void
    {
        $result = $this->executeParseResults('agg1', []);

        $this->assertArrayHasKey('agg1', $result);
        $this->assertInstanceOf(BucketCollection::class, $result['agg1']);
    }

    public function testParseEmptyResultsWithComposite(): void
    {
        $result = $this->executeParseResultsWithComposite('agg1', []);

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

    private function executeParseResultsWithComposite(string $aggName, ?array $buckets = null): array
    {
        if ($buckets === null) {
            $buckets = [
                [
                    'key' => 'tv',
                    'doc_count' => 4,
                    'score_max' => ['value' => 1],
                    'score_min' => ['value' => 1],
                ],
            ];
        }

        $response = [$aggName => [
            'doc_count_error_upper_bound' => 0,
            'buckets' => $buckets,
        ]];

        $composite = new MinMaxScoreAggregation();
        $testing = new TermsAggregation(
            name: $aggName,
            field: 'code',
            composite: $composite
        );

        return $testing->parseResults($response);
    }
}
