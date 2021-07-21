<?php

namespace Greensight\LaravelElasticQuery;

use Greensight\LaravelElasticQuery\Raw\Concerns\InteractsWithIndex;
use Greensight\LaravelElasticQuery\Raw\Contracts\SearchIndex;

abstract class ElasticIndex implements SearchIndex
{
    use InteractsWithIndex;

    protected string $name;

    protected string $tiebreaker;

    public function __construct()
    {
    }

    public function tiebreaker(): string
    {
        return $this->tiebreaker;
    }

    protected function indexName(): string
    {
        return $this->name;
    }
}
