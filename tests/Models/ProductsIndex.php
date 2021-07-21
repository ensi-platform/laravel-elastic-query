<?php

namespace Greensight\LaravelElasticQuery\Tests\Models;

use Greensight\LaravelElasticQuery\ElasticIndex;

class ProductsIndex extends ElasticIndex
{
    protected string $name = 'test_products';

    protected string $tiebreaker = 'product_id';
}
