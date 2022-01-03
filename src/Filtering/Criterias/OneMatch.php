<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Webmozart\Assert\Assert;

class OneMatch implements Criteria
{
    public function __construct(private string $field, private string $query, private string $operator)
    {
        Assert::stringNotEmpty(trim($field));
        Assert::oneOf($operator, ['and', 'or']);
    }

    public function toDSL(): array
    {
        $body = [
            'query' => $this->query,
            'operator' => $this->operator,
        ];

        return [
            'match' => [
                $this->field => $body,
            ],
        ];
    }
}