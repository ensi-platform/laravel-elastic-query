<?php

namespace Ensi\LaravelElasticQuery\Tests\Models;

use Ensi\LaravelElasticQuery\ElasticIndex;

class ProductsIndex extends ElasticIndex
{
    protected string $name = 'test_products';

    protected string $tiebreaker = 'product_id';

    public static function fullName(): string
    {
        return (new static())->indexName();
    }
}
