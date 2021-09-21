<?php

namespace Greensight\LaravelElasticQuery\Aggregating;

class MinMax
{
    public function __construct(public mixed $min, public mixed $max)
    {
    }
}
