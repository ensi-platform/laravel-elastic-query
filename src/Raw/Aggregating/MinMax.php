<?php

namespace Greensight\LaravelElasticQuery\Raw\Aggregating;

class MinMax
{
    public function __construct(public mixed $min, public mixed $max)
    {
    }
}
