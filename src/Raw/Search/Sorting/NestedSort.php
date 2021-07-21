<?php

namespace Greensight\LaravelElasticQuery\Raw\Search\Sorting;

use Greensight\LaravelElasticQuery\Raw\Contracts\Criteria;
use Greensight\LaravelElasticQuery\Raw\Contracts\DSLAware;

class NestedSort implements DSLAware
{
    public function __construct(
        private string $path,
        private Criteria $criteria,
        private ?NestedSort $nested = null
    ) {
    }

    public function toDSL(): array
    {
        $result = ['path' => $this->path];

        if ($filter = $this->criteria->toDSL()) {
            $result['filter'] = $filter;
        }

        if ($this->nested !== null) {
            $result['nested'] = $this->nested->toDSL();
        }

        return $result;
    }
}
