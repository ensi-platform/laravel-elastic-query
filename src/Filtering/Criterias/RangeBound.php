<?php

namespace Greensight\LaravelElasticQuery\Filtering\Criterias;

use Greensight\LaravelElasticQuery\Contracts\Criteria;
use Webmozart\Assert\Assert;

class RangeBound implements Criteria
{
    private static array $operators = [
        '>' => 'gt',
        '>=' => 'gte',
        '<' => 'lt',
        '<=' => 'lte',
    ];

    private string $operator;

    public function __construct(private string $field, string $operator, private mixed $value)
    {
        Assert::stringNotEmpty(trim($field));
        Assert::oneOf($operator, array_keys(self::$operators));

        $this->operator = self::$operators[$operator];
    }

    public function toDSL(): array
    {
        return ['range' => [$this->field => [$this->operator => $this->value]]];
    }
}
