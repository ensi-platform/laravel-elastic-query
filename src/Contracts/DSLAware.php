<?php

namespace Ensi\LaravelElasticQuery\Contracts;

interface DSLAware
{
    public function toDSL(): array;
}
