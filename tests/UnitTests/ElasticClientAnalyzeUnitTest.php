<?php

use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Tests\Data\Models\ProductsIndex;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('index analyze proxies call to elastic client', function () {
    /** @var UnitTestCase $this */

    $client = Mockery::mock(ElasticClient::class);
    $this->app->instance(ElasticClient::class, $client);

    $dsl = ['analyzer' => 'standard', 'text' => 'apple red'];
    $indexName = ProductsIndex::fullName();

    $client->shouldReceive('analyze')
        ->once()
        ->with($indexName, $dsl)
        ->andReturn(['tokens' => []]);

    $result = (new ProductsIndex())->analyze($dsl);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('tokens');
});
