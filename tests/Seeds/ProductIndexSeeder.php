<?php

namespace Greensight\LaravelElasticQuery\Tests\Seeds;

class ProductIndexSeeder extends IndexSeeder
{
    protected string $indexName = 'test_products';

    protected array $fixtures = ['products_default.json'];

    protected array $mappings = [
        'properties' => [
            'product_id' => ['type' => 'keyword'],
            'active' => ['type' => 'boolean'],

            'name' => ['type' => 'keyword', 'copy_to' => 'search_name'],
            'search_name' => ['type' => 'text'],
            'description' => ['type' => 'text'],

            'code' => ['type' => 'keyword'],
            'tags' => ['type' => 'keyword'],
            'rating' => ['type' => 'integer'],
            'package' => ['type' => 'keyword'],

            'offers' => [
                'type' => 'nested',
                'properties' => [
                    'seller_id' => ['type' => 'keyword'],
                    'active' => ['type' => 'boolean'],
                    'price' => ['type' => 'double'],
                ],
            ],
        ],
    ];
}
