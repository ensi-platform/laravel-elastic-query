<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Webmozart\Assert\Assert;

class Boosting implements Criteria
{
    public function __construct(
        private DSLAware $positive,
        private DSLAware $negative,
        private float $negativeBoost,
    ) {
        Assert::greaterThan($negativeBoost, 0);
        Assert::lessThanEq($negativeBoost, 1);
    }

    public function toDSL(): array
    {
        return [
            'boosting' => [
                'positive' => $this->positive->toDSL(),
                'negative' => $this->negative->toDSL(),
                'negative_boost' => $this->negativeBoost,
            ],
        ];
    }
}
