<?php

use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\ElasticQuery;
use Ensi\LaravelElasticQuery\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('elastic query get synonyms sets proxies call to elastic client', function () {
    /** @var UnitTestCase $this */

    $client = Mockery::mock(ElasticClient::class);
    $this->app->instance(ElasticClient::class, $client);

    $expected = [
        'count' => 1,
        'results' => [
            ['synonyms_set' => 'products-synonyms', 'count' => 2],
        ],
    ];

    $client->shouldReceive('getSynonymsSets')
        ->once()
        ->with(0, 20)
        ->andReturn($expected);

    $result = ElasticQuery::getSynonymsSets(0, 20);

    expect($result)->toBe($expected);
});

test('elastic query get synonym set proxies call to elastic client', function () {
    /** @var UnitTestCase $this */

    $client = Mockery::mock(ElasticClient::class);
    $this->app->instance(ElasticClient::class, $client);

    $expected = [
        'count' => 1,
        'synonyms_set' => [
            ['id' => 'rule-1', 'synonyms' => 'iphone, i-phone'],
        ],
    ];

    $client->shouldReceive('getSynonymSet')
        ->once()
        ->with('products-synonyms')
        ->andReturn($expected);

    $result = ElasticQuery::getSynonymSet('products-synonyms');

    expect($result)->toBe($expected);
});

test('elastic query put synonym set proxies call to elastic client', function () {
    /** @var UnitTestCase $this */

    $client = Mockery::mock(ElasticClient::class);
    $this->app->instance(ElasticClient::class, $client);

    $synonymsSet = [
        ['id' => 'rule-1', 'synonyms' => 'iphone, i-phone'],
        ['id' => 'rule-2', 'synonyms' => 'tv, television'],
    ];

    $expected = ['result' => 'created'];

    $client->shouldReceive('putSynonymSet')
        ->once()
        ->with('products-synonyms', $synonymsSet)
        ->andReturn($expected);

    $result = ElasticQuery::putSynonymSet('products-synonyms', $synonymsSet);

    expect($result)->toBe($expected);
});

test('elastic query delete synonym set proxies call to elastic client', function () {
    /** @var UnitTestCase $this */

    $client = Mockery::mock(ElasticClient::class);
    $this->app->instance(ElasticClient::class, $client);

    $expected = ['result' => 'deleted'];

    $client->shouldReceive('deleteSynonymSet')
        ->once()
        ->with('products-synonyms')
        ->andReturn($expected);

    $result = ElasticQuery::deleteSynonymSet('products-synonyms');

    expect($result)->toBe($expected);
});

test('elastic query get synonym rule proxies call to elastic client', function () {
    /** @var UnitTestCase $this */

    $client = Mockery::mock(ElasticClient::class);
    $this->app->instance(ElasticClient::class, $client);

    $expected = [
        'id' => 'rule-1',
        'synonyms' => 'iphone, i-phone',
    ];

    $client->shouldReceive('getSynonymRule')
        ->once()
        ->with('products-synonyms', 'rule-1')
        ->andReturn($expected);

    $result = ElasticQuery::getSynonymRule('products-synonyms', 'rule-1');

    expect($result)->toBe($expected);
});

test('elastic query put synonym rule proxies call to elastic client', function () {
    /** @var UnitTestCase $this */

    $client = Mockery::mock(ElasticClient::class);
    $this->app->instance(ElasticClient::class, $client);

    $expected = ['result' => 'updated'];

    $client->shouldReceive('putSynonymRule')
        ->once()
        ->with('products-synonyms', 'rule-1', 'iphone, i-phone')
        ->andReturn($expected);

    $result = ElasticQuery::putSynonymRule('products-synonyms', 'rule-1', 'iphone, i-phone');

    expect($result)->toBe($expected);
});

test('elastic query delete synonym rule proxies call to elastic client', function () {
    /** @var UnitTestCase $this */

    $client = Mockery::mock(ElasticClient::class);
    $this->app->instance(ElasticClient::class, $client);

    $expected = ['result' => 'deleted'];

    $client->shouldReceive('deleteSynonymRule')
        ->once()
        ->with('products-synonyms', 'rule-1')
        ->andReturn($expected);

    $result = ElasticQuery::deleteSynonymRule('products-synonyms', 'rule-1');

    expect($result)->toBe($expected);
});
