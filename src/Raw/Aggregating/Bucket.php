<?php

namespace Greensight\LaravelElasticQuery\Raw\Aggregating;

class Bucket
{
    public function __construct(public mixed $key, public int $count)
    {
    }
}
