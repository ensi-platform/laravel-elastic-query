<?php

namespace Greensight\LaravelElasticQuery\Filtering\Criterias;

use Greensight\LaravelElasticQuery\Contracts\Criteria;
use Webmozart\Assert\Assert;

class Term implements Criteria
{
    private mixed $value;

    public function __construct(private string $field, mixed $value)
    {
        Assert::stringNotEmpty(trim($field));

        $this->value = is_array($value) ? reset($value) : $value;
    }

    public function toDSL(): array
    {
        return ['term' => [$this->field => $this->value]];
    }
}
