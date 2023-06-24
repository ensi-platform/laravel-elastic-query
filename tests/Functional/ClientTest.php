<?php

namespace Ensi\LaravelElasticQuery\Tests\Functional;

use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Ensi\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;

class ClientTest extends ElasticTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ProductIndexSeeder::run();
    }

    public function testCatIndices(): void
    {
        $results = $this->newClient()->catIndices(ProductsIndex::fullName());

    }

    private function newClient(): ElasticClient
    {
        return resolve(ElasticClient::class);
    }
}
