<?php

use Ensi\LaravelElasticQuery\Aggregating\AggregationCollection;
use Ensi\LaravelElasticQuery\Aggregating\Bucket;
use Ensi\LaravelElasticQuery\Aggregating\Bucket\ReverseNestedAggregation;
use Ensi\LaravelElasticQuery\Aggregating\FiltersCollection;
use Ensi\LaravelElasticQuery\Aggregating\Metrics\CardinalityAggregation;
use Ensi\LaravelElasticQuery\Aggregating\Metrics\MinMaxScoreAggregation;
use Ensi\LaravelElasticQuery\Aggregating\Metrics\ScriptAggregation;
use Ensi\LaravelElasticQuery\Aggregating\Metrics\TopHitsAggregation;
use Ensi\LaravelElasticQuery\Aggregating\MinMax;
use Ensi\LaravelElasticQuery\Aggregating\Range;
use Ensi\LaravelElasticQuery\Contracts\AggregationsBuilder;
use Ensi\LaravelElasticQuery\Filtering\Criterias\RangeBound;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Term;
use Ensi\LaravelElasticQuery\Search\Sorting\Sort;
use Ensi\LaravelElasticQuery\Search\Sorting\SortCollection;
use Ensi\LaravelElasticQuery\Tests\Data\Models\ProductsIndex;
use Ensi\LaravelElasticQuery\Tests\IntegrationTestCase;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsCanonicalizing;
use function PHPUnit\Framework\assertGreaterThanOrEqual;

uses(IntegrationTestCase::class);

test('aggregation query get', function () {
    /** @var IntegrationTestCase $this */

    $results = ProductsIndex::aggregate()
        ->where('package', 'bottle')
        ->terms('codes', 'code')
        ->count('product_count', 'product_id')
        ->nested(
            'offers',
            fn (AggregationsBuilder $builder) => $builder
                ->where('seller_id', 10)
                ->minmax('price', 'price')
                ->min('min_price', 'price')
                ->max('max_price', 'price')
        )
        ->get();

    assertEqualsCanonicalizing(
        ['voda-san-pellegrino-mineralnaya-gazirovannaya', 'water'],
        $results->get('codes')->pluck('key')->all()
    );

    assertEquals(new MinMax(168.0, 611.0), $results->get('price'));
    assertEquals(168.0, $results->get('min_price'));
    assertEquals(611.0, $results->get('max_price'));

    assertEquals(2, $results->get('product_count'));
});

test('aggregation query composite', function () {
    /** @var IntegrationTestCase $this */

    $results = ProductsIndex::aggregate()
        ->composite(function (AggregationsBuilder $builder) {
            $builder->where('package', 'bottle')
                ->terms('codes', 'code');
        })
        ->get();

    assertEqualsCanonicalizing(
        ['voda-san-pellegrino-mineralnaya-gazirovannaya', 'water'],
        $results->get('codes')->pluck('key')->all()
    );
});

test('aggregation query top hits', function () {
    /** @var IntegrationTestCase $this */

    $results = ProductsIndex::aggregate()
        ->terms(
            name: 'group_by',
            field: 'active',
            composite: new TopHitsAggregation(
                'top_products',
                size: 10
            )
        )
        ->get();

    $results = $results->get('group_by');

    /** @var Bucket $result */
    foreach ($results as $result) {
        $groupByKey = $result->key;
        /** @var array $products */
        $products = $result->getCompositeValue('top_products');

        array_walk($products, fn ($hit) => assertEquals($groupByKey, data_get($hit, '_source.active')));
        $productIds = array_map(fn ($hit) => data_get($hit, '_source.product_id'), $products);

        assertEqualsCanonicalizing($groupByKey ? [1, 150, 328, 405, 471] : [319], $productIds);
    }
});

test('aggregation query cardinality', function () {
    /** @var IntegrationTestCase $this */

    $results = ProductsIndex::aggregate()
        ->cardinality('cardinality', 'active')
        ->get();

    assertEquals(2, $results->get('cardinality'));
});

test('aggregation query ranges', function () {
    /** @var IntegrationTestCase $this */

    $rangeFromTo = new Range(from: 0, to: 5, key: 'from-0-to-5');
    $rangeFrom = new Range(from: 7, key: 'from-7');
    $rangeTo = new Range(to: 8, key: 'to-8');

    $results = ProductsIndex::aggregate()
        ->ranges('ranges', 'rating', [$rangeFromTo, $rangeFrom, $rangeTo])
        ->get();

    /** @var Bucket $result */
    foreach ($results as $result) {
        $expected = match ($result->key) {
            'from-0-to-5' => 2,
            'from-7' => 3,
            'to-8' => 4,
            default => null,
        };

        assertEquals($expected, $result->count);
    }
});

test('aggregation query count all', function () {
    /** @var IntegrationTestCase $this */

    $results = ProductsIndex::aggregate()
        ->count('product_count', 'product_id')
        ->get();

    assertEquals(IntegrationTestCase::TOTAL_PRODUCTS, $results->get('product_count'));
});

test('aggregation query terms size', function () {
    /** @var IntegrationTestCase $this */

    $results = ProductsIndex::aggregate()
        ->where('package', 'bottle')
        ->terms('codes', 'code', 1)
        ->get();

    assertCount(1, $results->get('codes'));
});

test('aggregation query terms with sortBy composite value', function () {
    /** @var IntegrationTestCase $this */

    $sort = new Sort('score_max');
    $composite = new MinMaxScoreAggregation();

    $results = ProductsIndex::aggregate()
        ->whereMatch('description', 'water')
        ->where('package', 'bottle')
        ->terms(
            name: 'codes',
            field: 'code',
            size: 2,
            sort: $sort,
            composite: $composite
        )
        ->get();

    $scores = $results->get('codes')->map(
        fn (Bucket $bucket) => $bucket->getCompositeValue('score')->max
    );

    assertCount(2, $results->get('codes'));
    assertGreaterThanOrEqual($scores->first(), $scores->last());
});


test('aggregation query filters', function (?string $defaultBucket) {
    /** @var IntegrationTestCase $this */

    $filters = new FiltersCollection();
    $filters->add('filter_tags', new Term('tags', 'video'));
    $filters->add('filter_rating', new RangeBound('rating', '>=', 7));

    $topHits = new TopHitsAggregation('top_hits');

    $results = ProductsIndex::aggregate()
        ->filters('group_by_filters', $filters, $topHits, otherBucketKey: $defaultBucket)
        ->get()
        ->get('group_by_filters')
        ->keyBy(fn (Bucket $bucket) => $bucket->key);

    $additionResult = $defaultBucket !== null ? 1 : 0;
    assertCount($filters->count() + $additionResult, $results);

    assertEqualsCanonicalizing(
        [1, 328],
        extractBucketValues($results, 'filter_tags', $topHits->name(), 'product_id')
    );

    assertEqualsCanonicalizing(
        [1, 150, 405],
        extractBucketValues($results, 'filter_rating', $topHits->name(), 'product_id')
    );

    if ($defaultBucket != null) {
        assertEqualsCanonicalizing(
            [319, 471],
            extractBucketValues($results, $defaultBucket, $topHits->name(), 'product_id')
        );
    }
})->with([null, 'default_bucket']);

test('aggregation query by script', function () {
    /** @var IntegrationTestCase $this */
    $fieldName = 'max_tag_by_script';

    $sort = new SortCollection();
    $sort->add(new Sort($fieldName));

    $termAggregation = new AggregationCollection();
    $termAggregation->add(new ScriptAggregation(
        name: $fieldName,
        aggregationType: 'max',
        params: ['tag' => 'video'],
        source: '
          if (doc.containsKey("tags") 
            && doc["tags"].size() > 0
            && doc["tags"].contains(params.tag)
            ) {
                return 0;
            }
          return 1;
        ',
    ));

    $results = ProductsIndex::aggregate()
        ->terms(
            name: 'group_by',
            field: 'product_id',
            size: 3,
            sort: $sort,
            composite: $termAggregation
        )
        ->get()
        ->get('group_by');

    $scores = $results->map(
        fn (Bucket $bucket) => $bucket->getCompositeValue($fieldName)
    )->toArray();

    assertEqualsCanonicalizing(
        [1, 328, 150],
        $results->pluck('key')->toArray(),
    );

    assertEqualsCanonicalizing(
        [0.0, 0.0, 1.0],
        $scores,
    );
});

test('reverse nested aggregation with specific data', function () {
    /** @var IntegrationTestCase $this */

    $results = ProductsIndex::aggregate()
        ->where('active', true)
        ->nested('offers', function ($builder) {
            $reverseAggs = new AggregationCollection();
            $reverseAggs->add(new CardinalityAggregation(
                'product_id',
                'product_id'
            ));
            $reverseNested = new ReverseNestedAggregation('product_info', $reverseAggs);

            $compositeAggs = new AggregationCollection();
            $compositeAggs->add($reverseNested);

            $builder->terms('sellers', 'seller_id', 10, composite: $compositeAggs);
        })
        ->get();

    $sellers = $results->get('sellers');

    $seller10Bucket = $sellers->first(fn (Bucket $bucket) => $bucket->key == 10);
    expect($seller10Bucket)->not->toBeNull();
    expect($seller10Bucket->getCompositeValue('product_id'))->toBe(3);

    $seller15Bucket = $sellers->first(fn (Bucket $bucket) => $bucket->key == 15);
    expect($seller15Bucket)->not->toBeNull();
    expect($seller15Bucket->getCompositeValue('product_id'))->toBe(3);

    $seller20Bucket = $sellers->first(fn (Bucket $bucket) => $bucket->key == 20);
    expect($seller20Bucket)->not->toBeNull();
    expect($seller20Bucket->getCompositeValue('product_id'))->toBe(3);

    $seller90Bucket = $sellers->first(fn (Bucket $bucket) => $bucket->key == 90);
    expect($seller90Bucket)->not->toBeNull();
    expect($seller90Bucket->getCompositeValue('product_id'))->toBe(1);
});
