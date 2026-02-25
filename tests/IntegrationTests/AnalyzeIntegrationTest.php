<?php

use Ensi\LaravelElasticQuery\Tests\Data\Models\ProductsIndex;
use Ensi\LaravelElasticQuery\Tests\IntegrationTestCase;

uses(IntegrationTestCase::class);

test('analyze returns tokens', function () {
    $response = (new ProductsIndex())->analyze([
        'analyzer' => 'standard',
        'text' => 'apple red',
    ]);

    expect($response)->toBeArray();
    expect($response)->toHaveKey('tokens');
    expect($response['tokens'])->not->toBeEmpty();

    expect($response['tokens'][0])->toHaveKeys([
        'token',
        'start_offset',
        'end_offset',
        'type',
        'position',
    ]);
});
