<?php

namespace Greensight\LaravelElasticQuery\Raw\Filtering\Criterias;

use Greensight\LaravelElasticQuery\Raw\Contracts\Criteria;
use Webmozart\Assert\Assert;

class Exists implements Criteria
{
    public function __construct(private string $field)
    {
        Assert::stringNotEmpty(trim($field));
    }

    public function toDSL(): array
    {
        return ['exists' => ['field' => $this->field]];
    }
}