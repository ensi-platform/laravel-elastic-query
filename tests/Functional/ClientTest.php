<?php

namespace Ensi\LaravelElasticQuery\Tests\Functional;

use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Tests\AssertsArray;
use Ensi\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Ensi\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;

class ClientTest extends ElasticTestCase
{
    use AssertsArray;

    protected function setUp(): void
    {
        parent::setUp();

        ProductIndexSeeder::run();
    }

    public function testCatIndices(): void
    {
        $response = $this->newClient()->catIndices(ProductsIndex::fullName());

        $this->assertGreaterThanOrEqual(1, count($response));
        $this->assertArrayStructure([[
            'index',
            'status',
            'health',
            'uuid',
            'pri',
            'rep',
            'docs.count',
            'docs.deleted',
            'store.size',
            'pri.store.size',
        ]], $response);
    }

    public function testCatIndicesOnlySpecifiedFields(): void
    {
        $response = $this->newClient()->catIndices(ProductsIndex::fullName(), ['index', 'status']);

        $this->assertArrayStructure([['index', 'status']], $response);
        $this->assertArrayNotHasKey('health', $response[0]);
    }

    private function newClient(): ElasticClient
    {
        return resolve(ElasticClient::class);
    }
}
