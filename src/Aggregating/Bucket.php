<?php

namespace Ensi\LaravelElasticQuery\Aggregating;

class Bucket
{
    public function __construct(public mixed $key, public int $count, protected array $compositeValues = [])
    {
    }

    public function getCompositeValue(string $name): mixed
    {
        return $this->compositeValues[$name] ?? null;
    }
}
