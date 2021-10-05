<?php

namespace Ensi\LaravelElasticQuery;

use Ensi\LaravelElasticQuery\Concerns\InteractsWithIndex;
use Ensi\LaravelElasticQuery\Contracts\SearchIndex;

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
