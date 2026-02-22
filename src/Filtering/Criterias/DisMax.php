<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Webmozart\Assert\Assert;

class DisMax implements Criteria
{
    /**
     * @param DSLAware[] $queries
     */
    public function __construct(
        protected array $queries,
        protected float $tieBreaker = 0.0,
        protected ?float $boost = null,
    ) {
        Assert::notEmpty($queries);
        Assert::allIsInstanceOf($queries, DSLAware::class);
        Assert::greaterThanEq($tieBreaker, 0.0);
    }

    public function toDSL(): array
    {
        $dsl = [
            'queries' => array_map(
                static fn (DSLAware $q) => $q->toDSL(),
                $this->queries
            ),
        ];

        if ($this->tieBreaker > 0.0) {
            $dsl['tie_breaker'] = $this->tieBreaker;
        }

        if ($this->boost !== null) {
            $dsl['boost'] = $this->boost;
        }

        return ['dis_max' => $dsl];
    }
}
