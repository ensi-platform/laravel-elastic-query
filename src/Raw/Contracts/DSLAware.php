<?php

namespace Greensight\LaravelElasticQuery\Raw\Contracts;

interface DSLAware
{
    public function toDSL(): array;
}
