<?php

namespace Greensight\LaravelElasticQuery\Aggregating;

use Illuminate\Support\Collection;

class BucketCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
