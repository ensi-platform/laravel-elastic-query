<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\MatchOptions;
use Webmozart\Assert\Assert;

class OneMatch implements Criteria
{
    public function __construct(private string $field, private string $query, private MatchOptions $options)
    {
        Assert::stringNotEmpty(trim($field));
    }

    public function toDSL(): array
    {
        $body = ['query' => $this->query];

        return [
            'match' => [
                $this->field => array_merge($this->options->toArray(), $body),
            ],
        ];
    }
}
