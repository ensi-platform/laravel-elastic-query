<?php

namespace Ensi\LaravelElasticQuery\Aggregating;

class MinMax
{
    public function __construct(public mixed $min, public mixed $max)
    {
    }
}
