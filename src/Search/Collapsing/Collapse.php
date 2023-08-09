<?php

namespace Ensi\LaravelElasticQuery\Search\Collapsing;

use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Webmozart\Assert\Assert;

class Collapse implements DSLAware
{
    public function __construct(private string $field) {
        Assert::stringNotEmpty(trim($field));
    }

    public function field(): string
    {
        return $this->field;
    }

    public function toDSL(): array
    {
        return ['field' => $this->field];
    }
}
