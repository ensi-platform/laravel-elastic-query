<?php

namespace Ensi\LaravelElasticQuery\Search\Collapsing;

use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Ensi\LaravelElasticQuery\Search\Sorting\Sort;
use Webmozart\Assert\Assert;

class InnerHits implements DSLAware
{
    public function __construct(
        protected string $name,
        protected int $size,
        protected ?Sort $sort,
    ) {
        Assert::stringNotEmpty(trim($name));
    }

    public function toDSL(): array
    {
        $dsl = [
            'name' => $this->name,
            'size' => $this->size,
        ];

        if ($this->sort) {
            $dsl['sort'] = $this->sort->toDSL();
        }

        return $dsl;
    }
}
