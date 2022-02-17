<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
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
