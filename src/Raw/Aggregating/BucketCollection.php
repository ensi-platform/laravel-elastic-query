<?php

namespace Greensight\LaravelElasticQuery\Raw\Aggregating;

use Illuminate\Support\Collection;

class BucketCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
