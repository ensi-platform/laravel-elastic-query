<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\WildcardOptions;

class Wildcard implements Criteria
{
    public function __construct(private string $field, private string $query, private WildcardOptions $options)
    {
    }

    public function toDSL(): array
    {
        return ['wildcard' => [
            $this->field => array_merge($this->options->toArray(), [
                'value' => $this->query,
            ]),
        ]];
    }
}
