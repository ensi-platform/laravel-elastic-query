<?php

namespace Greensight\LaravelElasticQuery\Aggregating;

class Bucket
{
    public function __construct(public mixed $key, public int $count)
    {
    }
}
