<?php

namespace Greensight\LaravelElasticQuery\Raw\Filtering\Criterias;

use Greensight\LaravelElasticQuery\Raw\Contracts\Criteria;
use Webmozart\Assert\Assert;

class Term implements Criteria
{
    public function __construct(private string $field, private mixed $value)
    {
        Assert::stringNotEmpty(trim($field));
    }

    public function toDSL(): array
    {
        return ['term' => [$this->field => $this->value]];
    }
}
