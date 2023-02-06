<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request;

use Ensi\LaravelElasticQuery\Contracts\DSLAware;

interface Suggester extends DSLAware
{
    public function name(): string;
}
