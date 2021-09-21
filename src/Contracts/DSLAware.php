<?php

namespace Greensight\LaravelElasticQuery\Contracts;

interface DSLAware
{
    public function toDSL(): array;
}
