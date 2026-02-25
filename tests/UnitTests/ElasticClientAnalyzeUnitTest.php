<?php

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('elastic client analyze calls indices analyze and returns array', function () {
    $es = Mockery::mock(Client::class);
    $indices = Mockery::mock();

    $dsl = ['analyzer' => 'standard', 'text' => 'apple red'];
    $indexName = 'products_test';

    $response = Mockery::mock(Elasticsearch::class);
    $response->shouldReceive('asArray')->once()->andReturn(['tokens' => [['token' => 'apple']]]);

    $es->shouldReceive('indices')->once()->andReturn($indices);
    $indices->shouldReceive('analyze')
        ->once()
        ->with([
            'index' => $indexName,
            'body' => $dsl,
        ])
        ->andReturn($response);

    $client = new ElasticClient($es);

    $result = $client->analyze($indexName, $dsl);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('tokens');
});
