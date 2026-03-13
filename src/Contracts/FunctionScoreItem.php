<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Illuminate\Contracts\Support\Arrayable;

class FunctionScoreItem implements Arrayable
{
    public function __construct(
        protected ?float $weight = null,
        protected ?Criteria $filter = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'filter' => $this->filter?->toDSL(),
            'weight' => $this->weight,
        ], fn (mixed $value) => !is_null($value));
    }
}
