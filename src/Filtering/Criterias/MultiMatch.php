<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\MatchType;
use Webmozart\Assert\Assert;

class MultiMatch implements Criteria
{
    public function __construct(private array $fields, private string $query, private ?string $type = null)
    {
        Assert::nullOrOneOf($this->type, MatchType::cases());
    }

    public function toDSL(): array
    {
        $dsl = ['query' => $this->query];

        if ($this->fields) {
            $dsl['fields'] = $this->fields;
        }

        if ($this->type !== null) {
            $dsl['type'] = $this->type;
        }

        return ['multi_match' => $dsl];
    }
}
