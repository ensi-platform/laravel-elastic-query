<?php

namespace Greensight\LaravelElasticQuery\Filtering\Criterias;

use Greensight\LaravelElasticQuery\Contracts\Criteria;
use Webmozart\Assert\Assert;

class Nested implements Criteria
{
    public function __construct(private string $field, private Criteria $criteria)
    {
        Assert::stringNotEmpty(trim($field));
    }

    public function toDSL(): array
    {
        return [
            'nested' => [
                'path' => $this->field,
                'query' => $this->criteria->toDSL(),
            ],
        ];
    }
}
