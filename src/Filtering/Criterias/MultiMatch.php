<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\MultiMatchOptions;

class MultiMatch implements Criteria
{
    public function __construct(private array $fields, private string $query, private MultiMatchOptions $options)
    {
    }

    public function toDSL(): array
    {
        $dsl = ['query' => $this->query];

        if ($this->fields) {
            $dsl['fields'] = $this->fields;
        }

        return ['multi_match' => array_merge($this->options->toArray(), $dsl)];
    }
}
