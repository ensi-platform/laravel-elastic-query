<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Webmozart\Assert\Assert;

class OneMatch implements Criteria
{
    public function __construct(private string $field, private string $query)
    {
        Assert::stringNotEmpty(trim($field));
    }

    public function toDSL(): array
    {
        $body = ['query' => $this->query];

        return [
            'match' => [
                $this->field => $body,
            ],
        ];
    }
}