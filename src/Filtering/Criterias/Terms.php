<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Illuminate\Contracts\Support\Arrayable;
use Webmozart\Assert\Assert;

class Terms implements Criteria
{
    private array $values;

    public function __construct(private string $field, array|Arrayable $values)
    {
        Assert::stringNotEmpty(trim($field));

        $this->values = $values instanceof Arrayable ? $values->toArray() : $values;
    }

    public function toDSL(): array
    {
        return ['terms' => [$this->field => $this->values]];
    }
}